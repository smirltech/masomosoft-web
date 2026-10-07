<?php

namespace App\Http\Controllers\Api\V1\Pos;

use App\Enums\Devise;
use App\Http\Controllers\Controller;
use App\Models\Annee;
use App\Models\Eleve;
use App\Models\Frais;
use App\Models\Perception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class PosPaymentController extends Controller
{
    /**
     * Create POS payment.
     *
     * The payment reference is stored in perceptions.reference
     * and can later be retrieved using:
     *
     * GET /api/v1/pos/payments/{reference}
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            /*
            |--------------------------------------------------------------------------
            | PAYMENT IDENTIFIERS
            |--------------------------------------------------------------------------
            */

            'reference' => [
                'required',
                'string',
                'max:100',
            ],

            'client_payment_id' => [
                'nullable',
                'string',
                'max:100',
            ],

            'receipt_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'device_id' => [
                'required',
                'string',
                'max:100',
            ],

            /*
            |--------------------------------------------------------------------------
            | STUDENT
            |--------------------------------------------------------------------------
            */

            'student_id' => [
                'required',
                'string',
            ],

            /*
            |--------------------------------------------------------------------------
            | PAYMENT ITEMS
            |--------------------------------------------------------------------------
            */

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.fee_id' => [
                'required',
                'integer',
                'distinct',
            ],

            'items.*.amount' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'items.*.period' => [
                'nullable',
                'string',
                'max:100',
            ],

            /*
            |--------------------------------------------------------------------------
            | PAYMENT
            |--------------------------------------------------------------------------
            */

            'currency' => [
                'required',
                'string',
                'in:USD,CDF',
            ],

            'payment_method' => [
                'required',
                'string',
                'in:cash,bank_transfer,mobile_money,card',
            ],

            'paid_at' => [
                'required',
                'date',
            ],
        ]);



        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }



        $annee = Annee::id();

        if (! $annee) {
            return response()->json([
                'message' => 'No current academic year found.',
            ], 422);
        }


        $paymentCurrency = strtoupper(
            trim((string) $validated['currency'])
        );


        $reference = trim(
            (string) $validated['reference']
        );




        $existingPayment = Perception::query()
            ->where('reference', $reference)
            ->first();

        if ($existingPayment) {
            return response()->json([
                'message' => 'This payment reference has already been processed.',

                'reference' => $reference,

                'payment' => [
                    'id' => $existingPayment->id,
                    'reference' => $existingPayment->reference,
                    'amount' => (float) $existingPayment->montant,
                    'currency' => $this->normalizeCurrency(
                        $existingPayment->devise
                    ),
                    'paid_at' => $existingPayment->paid_at,
                ],
            ], 409);
        }


        try {
            $result = DB::transaction(function () use (
                $validated,
                $annee,
                $user,
                $paymentCurrency,
                $reference
            ) {


                $studentIdentifier = trim(
                    (string) $validated['student_id']
                );



                $student = Eleve::query()
                    ->where(function ($query) use ($studentIdentifier) {
                        $query
                            ->where(
                                'id',
                                $studentIdentifier
                            )
                            ->orWhere(
                                'matricule',
                                $studentIdentifier
                            )
                            ->orWhere(
                                'numero_permanent',
                                $studentIdentifier
                            );
                    })
                    ->first();

                if (! $student) {
                    throw ValidationException::withMessages([
                        'student_id' => [
                            'Student not found.',
                        ],
                    ]);
                }



                $inscription = $student
                    ->inscriptions()
                    ->with('classe')
                    ->where(
                        'annee_id',
                        $annee->id
                    )
                    ->first();

                if (! $inscription) {
                    throw ValidationException::withMessages([
                        'student_id' => [
                            'Student is not registered for the current academic year.',
                        ],
                    ]);
                }



                $duplicate = Perception::query()
                    ->where(
                        'reference',
                        $reference
                    )
                    ->lockForUpdate()
                    ->first();

                if ($duplicate) {
                    throw ValidationException::withMessages([
                        'reference' => [
                            'This payment reference has already been processed.',
                        ],
                    ]);
                }


                $payments = [];

                $totalPaid = 0;


                foreach (
                    $validated['items']
                    as $index => $item
                ) {


                    $fee = Frais::query()
                        ->where(
                            'id',
                            $item['fee_id']
                        )
                        ->where(function ($query) use ($annee) {
                            $query
                                ->where(
                                    'annee_id',
                                    $annee->id
                                )
                                ->orWhereNull(
                                    'annee_id'
                                );
                        })
                        ->lockForUpdate()
                        ->first();

                    if (! $fee) {
                        throw ValidationException::withMessages([
                            "items.$index.fee_id" => [
                                'Fee is not available for the current academic year.',
                            ],
                        ]);
                    }



                    $feeCurrency = $fee->devise instanceof Devise
                        ? $fee->devise->value
                        : (string) $fee->devise;

                    $feeCurrency = strtoupper(
                        trim(
                            $feeCurrency ?: 'USD'
                        )
                    );



                    if ($feeCurrency !== $paymentCurrency) {
                        throw ValidationException::withMessages([
                            'currency' => [
                                'The payment currency does not match fee '
                                . $fee->id
                                . '. Expected '
                                . $feeCurrency
                                . '.',
                            ],
                        ]);
                    }



                    $amountDue = (float) $fee->montant;

                    if ($amountDue <= 0) {
                        throw ValidationException::withMessages([
                            "items.$index.fee_id" => [
                                'The selected fee has an invalid amount.',
                            ],
                        ]);
                    }



                    $period = $item['period'] ?? null;



                    $paidQuery = Perception::query()
                        ->where(
                            'inscription_id',
                            $inscription->id
                        )
                        ->where(
                            'annee_id',
                            $annee->id
                        )
                        ->where(
                            'frais_id',
                            $fee->id
                        )
                        ->where(
                            'devise',
                            $paymentCurrency
                        );

                    if ($period !== null) {
                        $paidQuery->where(
                            'custom_property',
                            $period
                        );
                    }

                    $existingPerceptions = $paidQuery
                        ->lockForUpdate()
                        ->get();

                    $amountPaid = (float) $existingPerceptions->sum(
                        fn (Perception $perception) =>
                        (float) $perception->montant
                    );


                    $outstanding = max(
                        $amountDue - $amountPaid,
                        0
                    );


                    $requestedAmount = (float) $item['amount'];



                    if ($requestedAmount > $outstanding) {
                        throw ValidationException::withMessages([
                            "items.$index.amount" => [
                                'The payment amount for fee '
                                . $fee->id
                                . ' exceeds the outstanding balance of '
                                . $outstanding
                                . ' '
                                . $paymentCurrency
                                . '.',
                            ],
                        ]);
                    }



                    $perception = new Perception();


                    $perception->reference = $reference;

                    $perception->user_id =
                        $user->id;

                    $perception->frais_id =
                        $fee->id;

                    $perception->inscription_id =
                        $inscription->id;

                    $perception->annee_id =
                        $annee->id;

                    $perception->custom_property =
                        $period;

                    $perception->montant =
                        $requestedAmount;


                    $perception->taux = 1;


                    $perception->devise =
                        $paymentCurrency;



                    $perception->frais_montant =
                        $amountDue;


                    $perception->paid_by =
                        $validated['payment_method'];


                    $perception->paid_at =
                        $validated['paid_at'];


                    $perception->due_date =
                        now()->toDateString();



                    $perception->save();



                    $newAmountPaid =
                        $amountPaid +
                        $requestedAmount;

                    $newOutstanding = max(
                        $amountDue -
                        $newAmountPaid,
                        0
                    );



                    if ($newAmountPaid <= 0) {
                        $status = 'unpaid';
                    } elseif ($newAmountPaid < $amountDue) {
                        $status = 'partial';
                    } else {
                        $status = 'paid';
                    }


                    $payments[] = [
                        'id' =>
                            $perception->id,

                        'reference' =>
                            $perception->reference,

                        'fee' => [
                            'id' =>
                                $fee->id,

                            'name' =>
                                trim(
                                    (string) $fee->nom
                                ),
                        ],

                        'period' =>
                            $period,

                        'amount_due' =>
                            $amountDue,

                        'amount' =>
                            $requestedAmount,

                        'amount_paid' =>
                            $newAmountPaid,

                        'outstanding' =>
                            $newOutstanding,

                        'currency' =>
                            $paymentCurrency,

                        'status' =>
                            $status,

                        'payment_method' =>
                            $validated['payment_method'],

                        'paid_at' =>
                            $perception->paid_at,
                    ];

                    $totalPaid +=
                        $requestedAmount;
                }



                return [
                    'student' =>
                        $student,

                    'inscription' =>
                        $inscription,

                    'payments' =>
                        $payments,

                    'total_paid' =>
                        $totalPaid,
                ];
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' =>
                    'Payment could not be processed.',
            ], 500);
        }

        return response()->json([
            'data' => [


                'id' =>
                    $result['payments'][0]['id'] ?? null,

                'reference' =>
                    $reference,

                'client_payment_id' =>
                    $validated['client_payment_id'] ?? null,

                'receipt_number' =>
                    $validated['receipt_number'] ?? null,

                'device_id' =>
                    $validated['device_id'],

                'status' =>
                    'completed',



                'student' => [
                    'id' =>
                        $result['student']->id,

                    'name' =>
                        trim(
                            (string) $result['student']->nom
                        ),

                    'matricule' =>
                        $result['student']->matricule,

                    'class' =>
                        $result['inscription']
                            ->classe
                            ?->code,
                ],



                'amount' =>
                    $result['total_paid'],

                'currency' =>
                    strtoupper(
                        $validated['currency']
                    ),



                'payment_method' =>
                    $validated['payment_method'],



                'paid_at' =>
                    $validated['paid_at'],



                'items' =>
                    $result['payments'],
            ],
        ], 201);
    }


    public function show(string $reference): JsonResponse
    {


        $reference = trim($reference);



        $payment = Perception::query()
            ->with([
                'frais',
                'inscription.classe',
                'inscription.eleve',
                'user',
            ])
            ->where(
                'reference',
                $reference
            )
            ->first();



        if (! $payment) {
            return response()->json([
                'message' =>
                    'Payment not found.',

                'reference' =>
                    $reference,
            ], 404);
        }



        $inscription =
            $payment->inscription;

        $student =
            $inscription?->eleve;

        $fee =
            $payment->frais;



        $studentName = $student
            ? trim(
                (string) $student->nom
            )
            : null;



        $classCode =
            $inscription?->classe_code?->code;

        /*
        |--------------------------------------------------------------------------
        | CURRENCY
        |--------------------------------------------------------------------------
        */

        $currency =
            $this->normalizeCurrency(
                $payment->devise
            );



        $operator = null;

        if ($payment->user) {
            $operator = [
                'id' =>
                    $payment->user->id,

                'name' =>
                    $this->userDisplayName(
                        $payment->user
                    ),
            ];
        }


        $synchronization = [
            'status' =>
                'synchronized',

            'pending' =>
                false,
        ];



        return response()->json([
            'data' => [


                'id' =>
                    $payment->id,

                'reference' =>
                    $payment->reference,

                'receipt_number' =>
                    $payment->reference,




                'status' =>
                    'completed',



                'synchronization' =>
                    $synchronization,



                'school' => [
                    'name' =>
                        config(
                            'app.name',
                            'MasomoSoft'
                        ),
                ],



                'date_time' =>
                    $payment->paid_at,


                'student' => [
                    'id' =>
                        $student?->id,

                    'name' =>
                        $studentName,

                    'matricule' =>
                        $student?->matricule,

                    'class' =>
                        $classCode,
                ],



                'fees' => [
                    [
                        'id' =>
                            $fee?->id,

                        'name' =>
                            $fee
                                ? trim(
                                (string) $fee->nom
                            )
                                : null,

                        'amount' =>
                            (float) $payment->frais_montant,
                    ],
                ],



                'amount' =>
                    (float) $payment->montant,



                'currency' =>
                    $currency,


                'payment_method' =>
                    $payment->paid_by,



                'teller' =>
                    $operator,
            ],
        ]);
    }


    private function normalizeCurrency($currency): string
    {
        if ($currency instanceof Devise) {
            $currency = $currency->value;
        }

        return strtoupper(
            trim(
                (string) $currency
            )
        );
    }


    private function userDisplayName($user): string
    {

           return $user->name;

    }
}
