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

    public function store(Request $request): JsonResponse
    {


        $validated = $request->validate([
            'client_payment_id' => [
                'required',
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

            'student_id' => [
                'required',
                'string',
            ],

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



        $annee = Annee::encours();

        if (! $annee) {
            return response()->json([
                'message' => 'No current academic year found.',
            ], 422);
        }



        $paymentCurrency = strtoupper(
            (string) $validated['currency']
        );



        $existingPayment = Perception::query()
            ->where(
                'reference',
                $validated['client_payment_id']
            )
            ->first();

        if ($existingPayment) {
            return response()->json([
                'message' => 'This payment has already been processed.',

                'client_payment_id' =>
                    $validated['client_payment_id'],

                'payment' => [
                    'id' =>
                        $existingPayment->id,

                    'reference' =>
                        $existingPayment->reference,
                ],
            ], 409);
        }

        /*
        |--------------------------------------------------------------------------
        | Transaction
        |--------------------------------------------------------------------------
        */

        try {
            $result = DB::transaction(function () use (
                $validated,
                $annee,
                $user,
                $paymentCurrency
            ) {

                /*
                |--------------------------------------------------------------------------
                | Find student
                |--------------------------------------------------------------------------
                */

                $studentIdentifier =
                    trim(
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

                /*
                |--------------------------------------------------------------------------
                | Current academic enrollment
                |--------------------------------------------------------------------------
                */

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

                /*
                |--------------------------------------------------------------------------
                | Second duplicate check inside transaction
                |
                | Protects against two POS devices sending the same payment
                | simultaneously.
                |--------------------------------------------------------------------------
                */

                $duplicate = Perception::query()
                    ->where(
                        'reference',
                        $validated['client_payment_id']
                    )
                    ->lockForUpdate()
                    ->first();

                if ($duplicate) {
                    throw ValidationException::withMessages([
                        'client_payment_id' => [
                            'This payment has already been processed.',
                        ],
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Payment containers
                |--------------------------------------------------------------------------
                */

                $payments = [];

                $totalPaid = 0;

                /*
                |--------------------------------------------------------------------------
                | Process each payment item
                |--------------------------------------------------------------------------
                */

                foreach (
                    $validated['items']
                    as $index => $item
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Find and lock fee
                    |--------------------------------------------------------------------------
                    */

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

                    /*
                    |--------------------------------------------------------------------------
                    | Fee currency
                    |--------------------------------------------------------------------------
                    */

                    $feeCurrency = $fee->devise instanceof Devise
                        ? $fee->devise->value
                        : (string) $fee->devise;

                    $feeCurrency = strtoupper(
                        trim(
                            $feeCurrency ?: 'USD'
                        )
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Currency validation
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $feeCurrency !==
                        $paymentCurrency
                    ) {
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

                    /*
                    |--------------------------------------------------------------------------
                    | Fee amount
                    |--------------------------------------------------------------------------
                    */

                    $amountDue = (float) $fee->montant;

                    if ($amountDue <= 0) {
                        throw ValidationException::withMessages([
                            "items.$index.fee_id" => [
                                'The selected fee has an invalid amount.',
                            ],
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Period
                    |--------------------------------------------------------------------------
                    */

                    $period =
                        $item['period'] ?? null;

                    /*
                    |--------------------------------------------------------------------------
                    | Existing payments
                    |--------------------------------------------------------------------------
                    */

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

                    /*
                    |--------------------------------------------------------------------------
                    | Lock existing perceptions
                    |--------------------------------------------------------------------------
                    */

                    $existingPerceptions =
                        $paidQuery
                            ->lockForUpdate()
                            ->get();

                    /*
                    |--------------------------------------------------------------------------
                    | Already paid
                    |--------------------------------------------------------------------------
                    */

                    $amountPaid =
                        (float) $existingPerceptions->sum(
                            fn (Perception $perception) =>
                            (float) $perception->montant
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | Outstanding
                    |--------------------------------------------------------------------------
                    */

                    $outstanding =
                        max(
                            $amountDue -
                            $amountPaid,
                            0
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | Requested payment
                    |--------------------------------------------------------------------------
                    */

                    $requestedAmount =
                        (float) $item['amount'];

                    /*
                    |--------------------------------------------------------------------------
                    | Prevent overpayment
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $requestedAmount >
                        $outstanding
                    ) {
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

                    /*
                    |--------------------------------------------------------------------------
                    | Create perception
                    |--------------------------------------------------------------------------
                    |
                    | API client_payment_id is stored as "reference".
                    |
                    |--------------------------------------------------------------------------
                    */

                    $perception =
                        new Perception();

                    $perception->reference =
                        $validated['client_payment_id'];

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

                    /*
                    |--------------------------------------------------------------------------
                    | Exchange rate
                    |--------------------------------------------------------------------------
                    |
                    | No rate is sent by the current API.
                    | For same-currency payments, 1 is appropriate.
                    |
                    |--------------------------------------------------------------------------
                    */

                    $perception->taux =
                        1;

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

                    /*
                    |--------------------------------------------------------------------------
                    | New financial state
                    |--------------------------------------------------------------------------
                    */

                    $newAmountPaid =
                        $amountPaid +
                        $requestedAmount;

                    $newOutstanding =
                        max(
                            $amountDue -
                            $newAmountPaid,
                            0
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | Status
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $newAmountPaid <= 0
                    ) {
                        $status = 'unpaid';
                    } elseif (
                        $newAmountPaid <
                        $amountDue
                    ) {
                        $status = 'partial';
                    } else {
                        $status = 'paid';
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Payment response item
                    |--------------------------------------------------------------------------
                    */

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

        /*
        |--------------------------------------------------------------------------
        | Successful response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'data' => [
                'id' =>
                    $result['payments'][0]['id'] ?? null,

                'client_payment_id' =>
                    $validated['client_payment_id'],

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
}
