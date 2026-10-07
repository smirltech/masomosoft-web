<?php

namespace App\Http\Controllers\Api\V1\Pos;

use App\Enums\Devise;
use App\Http\Controllers\Controller;
use App\Models\Eleve;
use App\Models\Perception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class PosPaymentController extends Controller
{
    /**
     * Create POS payment.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_payment_id' => [
                'required',
                'string',
                'max:100',
            ],

            'device_id' => [
                'required',
                'string',
                'max:100',
            ],

            /*
             * Peut être un ID numérique,
             * un matricule ou un numéro permanent.
             */
            'student_id' => [
                'required',
                'string',
                'max:100',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.fee_id' => [
                'required',
                'integer',
            ],

            'items.*.amount' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'currency' => [
                'required',
                'string',
                'size:3',
            ],

            'payment_method' => [
                'required',
                'string',
                'in:cash',
            ],
        ]);

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | NORMALIZE CURRENCY
        |--------------------------------------------------------------------------
        */

        $paymentCurrency = strtoupper(
            trim($validated['currency'])
        );

        if (!in_array($paymentCurrency, ['USD', 'CDF'], true)) {
            return response()->json([
                'message' => 'Unsupported currency.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | TRANSACTION
        |--------------------------------------------------------------------------
        */

        try {
            return DB::transaction(function () use (
                $validated,
                $user,
                $paymentCurrency
            ) {

                /*
                |--------------------------------------------------------------------------
                | 1. IDEMPOTENCY
                |--------------------------------------------------------------------------
                |
                | Si le POS renvoie le même paiement à cause d'un problème
                | réseau, on ne crée pas une deuxième perception.
                |
                */

                $existingPayment = Perception::query()
                    ->where(
                        'custom_property->client_payment_id',
                        $validated['client_payment_id']
                    )
                    ->first();

                if ($existingPayment) {
                    return response()->json([
                        'data' => $this->paymentResponse(
                            $existingPayment
                        ),
                    ], 200);
                }

                /*
                |--------------------------------------------------------------------------
                | 2. FIND STUDENT
                |--------------------------------------------------------------------------
                |
                | student_id peut correspondre à :
                |
                | - eleve.id
                | - matricule
                | - numero_permanent
                |
                */

                $studentIdentifier = trim(
                    (string) $validated['student_id']
                );

                $studentQuery = Eleve::query()
                    ->lockForUpdate();

                if (ctype_digit($studentIdentifier)) {

                    $studentQuery->where(function ($query) use ($studentIdentifier) {

                        $query
                            ->where('id', (int) $studentIdentifier)
                            ->orWhere(
                                'matricule',
                                $studentIdentifier
                            )
                            ->orWhere(
                                'numero_permanent',
                                $studentIdentifier
                            );

                    });

                } else {

                    $studentQuery->where(function ($query) use ($studentIdentifier) {

                        $query
                            ->where(
                                'matricule',
                                $studentIdentifier
                            )
                            ->orWhere(
                                'numero_permanent',
                                $studentIdentifier
                            );

                    });
                }

                $student = $studentQuery->first();

                if (!$student) {
                    return response()->json([
                        'message' => 'Student does not exist.',

                        'student_id' =>
                            $validated['student_id'],
                    ], 422);
                }

                /*
                |--------------------------------------------------------------------------
                | 3. FIND CURRENT ENROLLMENT
                |--------------------------------------------------------------------------
                */

                $inscriptionQuery = $student->inscriptions();

                /*
                 * Si l'utilisateur possède une école,
                 * on limite à son école.
                 */
                if (!empty($user->school_id)) {
                    $inscriptionQuery->where(
                        'schools.id',
                        $user->school_id
                    );
                }

                /*
                 * Année académique de l'utilisateur.
                 */
                if (!empty($user->annee_id)) {
                    $inscriptionQuery->where(
                        'annee_id',
                        $user->annee_id
                    );
                }

                $inscription = $inscriptionQuery
                    ->lockForUpdate()
                    ->first();

                if (!$inscription) {
                    return response()->json([
                        'message' =>
                            'Student does not have a valid enrollment for the current academic year.',

                        'student' => [
                            'id' => $student->id,

                            'name' =>
                                $this->studentName($student),

                            'matricule' =>
                                $student->matricule,

                            'numero_permanent' =>
                                $student->numero_permanent,
                        ],
                    ], 422);
                }

                /*
                |--------------------------------------------------------------------------
                | 4. SERVER DATE
                |--------------------------------------------------------------------------
                */

                $paidAt = now();

                /*
                |--------------------------------------------------------------------------
                | 5. RECEIPT NUMBER
                |--------------------------------------------------------------------------
                */

                $receiptNumber =
                    $this->generateReceiptNumber();

                /*
                |--------------------------------------------------------------------------
                | 6. CREATE PAYMENTS
                |--------------------------------------------------------------------------
                */

                $createdPayments = [];

                $totalAmount = 0;

                foreach ($validated['items'] as $item) {

                    /*
                    |--------------------------------------------------------------------------
                    | FIND FEE
                    |--------------------------------------------------------------------------
                    */

                    $fee = DB::table('frais')
                        ->where('id', $item['fee_id'])
                        ->lockForUpdate()
                        ->first();

                    if (!$fee) {
                        return response()->json([
                            'message' =>
                                "Fee {$item['fee_id']} does not exist.",
                        ], 422);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | FEE APPLICABILITY
                    |--------------------------------------------------------------------------
                    */

                    if (!$this->feeIsApplicable(
                        $fee,
                        $student,
                        $inscription
                    )) {
                        return response()->json([
                            'message' =>
                                "Fee {$item['fee_id']} is not applicable to this student.",
                        ], 422);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | FEE CURRENCY
                    |--------------------------------------------------------------------------
                    */

                    $feeCurrency = $this->normalizeCurrency(
                        $fee->devise
                    );

                    if ($feeCurrency !== $paymentCurrency) {
                        return response()->json([
                            'message' =>
                                'The payment currency does not match the fee currency.',

                            'fee' => [
                                'id' => $fee->id,
                                'currency' => $feeCurrency,
                            ],

                            'payment' => [
                                'currency' => $paymentCurrency,
                            ],
                        ], 422);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | OUTSTANDING BALANCE
                    |--------------------------------------------------------------------------
                    */

                    $outstandingBalance =
                        $this->calculateOutstandingBalance(
                            $fee,
                            $inscription
                        );

                    $requestedAmount =
                        round(
                            (float) $item['amount'],
                            2
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | PREVENT OVERPAYMENT
                    |--------------------------------------------------------------------------
                    */

                    if ($requestedAmount > $outstandingBalance) {

                        return response()->json([
                            'message' =>
                                "Payment amount exceeds the outstanding balance for fee {$fee->id}.",

                            'fee' => [
                                'id' => $fee->id,

                                'name' =>
                                    trim((string) $fee->nom),

                                'amount' =>
                                    (float) $fee->montant,

                                'already_paid' =>
                                    round(
                                        (float) $fee->montant
                                        - $outstandingBalance,
                                        2
                                    ),

                                'outstanding' =>
                                    $outstandingBalance,

                                'currency' =>
                                    $feeCurrency,
                            ],

                            'requested_amount' =>
                                $requestedAmount,

                        ], 422);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | ZERO BALANCE
                    |--------------------------------------------------------------------------
                    */

                    if ($outstandingBalance <= 0) {
                        return response()->json([
                            'message' =>
                                "Fee {$fee->id} is already fully paid.",

                            'fee_id' =>
                                $fee->id,
                        ], 422);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | CREATE PERCEPTION
                    |--------------------------------------------------------------------------
                    */

                    $payment = Perception::create([

                        'reference' =>
                            $receiptNumber,

                        'user_id' =>
                            $user->id,

                        'frais_id' =>
                            $fee->id,

                        'inscription_id' =>
                            $inscription->id,

                        'annee_id' =>
                            $inscription->annee_id,

                        'custom_property' => [

                            'client_payment_id' =>
                                $validated['client_payment_id'],

                            'device_id' =>
                                $validated['device_id'],

                            'student_id' =>
                                $student->id,

                            'payment_method' =>
                                $validated['payment_method'],

                        ],

                        'montant' =>
                            $requestedAmount,

                        'taux' =>
                            $fee->taux ?? 1,

                        'devise' =>
                            $paymentCurrency,

                        'frais_montant' =>
                            $fee->montant,

                        'paid_by' =>
                            $student->id,

                        'paid_at' =>
                            $paidAt,

                        'due_date' =>
                            $fee->due_date ?? null,
                    ]);

                    $createdPayments[] = $payment;

                    $totalAmount += $requestedAmount;
                }



                $payment = $createdPayments[0];

                return response()->json([
                    'data' => [

                        'id' =>
                            $payment->id,

                        'client_payment_id' =>
                            $validated['client_payment_id'],

                        'device_id' =>
                            $validated['device_id'],

                        'receipt_number' =>
                            $receiptNumber,

                        'status' =>
                            'completed',

                        'student' => [

                            'id' =>
                                $student->id,

                            'name' =>
                                $this->studentName($student),

                            'matricule' =>
                                $student->matricule,

                            'numero_permanent' =>
                                $student->numero_permanent,

                        ],

                        'amount' =>
                            round($totalAmount, 2),

                        'currency' =>
                            $paymentCurrency,

                        'payment_method' =>
                            $validated['payment_method'],

                        'paid_at' =>
                            $paidAt->toIso8601String(),

                        'items' =>
                            collect($createdPayments)
                                ->map(function ($payment) {

                                    return [
                                        'id' =>
                                            $payment->id,

                                        'fee_id' =>
                                            $payment->frais_id,

                                        'amount' =>
                                            (float) $payment->montant,

                                        'currency' =>
                                            $this->normalizeCurrency(
                                                $payment->devise
                                            ),
                                    ];

                                })
                                ->values(),

                    ],
                ], 201);
            });

        } catch (Throwable $exception) {

            report($exception);

            return response()->json([
                'message' =>
                    'Payment could not be processed.',

                'error' =>
                    $exception->getMessage(),

            ], 500);
        }
    }


    /**
     * Generate unique receipt number.
     */
    private function generateReceiptNumber(): string
    {
        do {

            $number =
                'REC-' .
                now()->format('Y') .
                '-' .
                str_pad(
                    (string) random_int(
                        1,
                        9999999
                    ),
                    7,
                    '0',
                    STR_PAD_LEFT
                );

        } while (
            Perception::query()
                ->where(
                    'reference',
                    $number
                )
                ->exists()
        );

        return $number;
    }


    /**
     * Check whether fee applies to student.
     */
    private function feeIsApplicable(
        $fee,
        $student,
        $inscription
    ): bool {


        if (
            !empty($fee->annee_id)
            &&
            (int) $fee->annee_id
            !==
            (int) $inscription->annee_id
        ) {
            return false;
        }

        return true;
    }


    /**
     * Calculate outstanding balance.
     */
    private function calculateOutstandingBalance(
        $fee,
        $inscription
    ): float {

        $alreadyPaid = Perception::query()
            ->where(
                'frais_id',
                $fee->id
            )
            ->where(
                'inscription_id',
                $inscription->id
            )
            ->where(
                'annee_id',
                $inscription->annee_id
            )
            ->sum('montant');

        $feeAmount =
            (float) $fee->montant;

        $balance =
            $feeAmount
            -
            (float) $alreadyPaid;

        return max(
            0,
            round(
                $balance,
                2
            )
        );
    }


    /**
     * Idempotent payment response.
     */
    private function paymentResponse(
        Perception $payment
    ): array {

        $customProperty =
            is_array($payment->custom_property)
                ? $payment->custom_property
                : [];

        return [

            'id' =>
                $payment->id,

            'client_payment_id' =>
                data_get(
                    $customProperty,
                    'client_payment_id'
                ),

            'device_id' =>
                data_get(
                    $customProperty,
                    'device_id'
                ),

            'receipt_number' =>
                $payment->reference,

            'status' =>
                'completed',

            'student' => [

                'id' =>
                    $payment->paid_by,

            ],

            'amount' =>
                (float) $payment->montant,

            'currency' =>
                $this->normalizeCurrency(
                    $payment->devise
                ),

            'payment_method' =>
                data_get(
                    $customProperty,
                    'payment_method'
                ),

            'paid_at' =>
                optional(
                    $payment->paid_at
                )->toIso8601String(),
        ];
    }



    private function studentName($student): string
    {
        return trim(
            implode(
                ' ',
                array_filter([
                    $student->nom ?? null,
                ])
            )
        );
    }


    private function normalizeCurrency(
        $currency
    ): string {

        if ($currency instanceof Devise) {
            return strtoupper(
                $currency->value
            );
        }

        if ($currency instanceof \BackedEnum) {
            return strtoupper(
                (string) $currency->value
            );
        }

        return strtoupper(
            trim(
                (string) $currency
            )
        );
    }
}
