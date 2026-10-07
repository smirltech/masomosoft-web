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

    public function store(Request $request)
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

            'student_id' => [
                'required',
                'integer',
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

        return DB::transaction(function () use ($validated, $request) {

            /*
             * 1. Vérifier que le device est autorisé.
             */
            $user = $request->user();

            if (!$user) {
                abort(401, 'Unauthenticated.');
            }

            /*
             * Ici tu peux ajouter ta vérification :
             *
             * $this->authorizeDevice($user, $validated['device_id']);
             */


            /*
             * 2. Vérifier que le paiement n'existe pas déjà.
             *
             * Très important pour éviter qu'un retry réseau
             * crée deux paiements.
             */
            $existingPayment = Perception::query()
                ->where('custom_property->client_payment_id', $validated['client_payment_id'])
                ->first();

            if ($existingPayment) {
                return response()->json([
                    'data' => $this->paymentResponse($existingPayment),
                ], 200);
            }



            $student = Eleve::query()
                ->where('id', $validated['student_id'])
                ->lockForUpdate()
                ->first();

            if (!$student) {
                abort(422, 'Student does not exist.');
            }



            $inscription = $student->inscriptions()
                ->where('schools.id', $user->school_id)
                ->where('annee_id', $user->annee_id)
                ->lockForUpdate()
                ->first();

            if (!$inscription) {
                abort(
                    422,
                    'Student does not have a valid enrollment for the current academic year.'
                );
            }


            $paidAt = now();

            $receiptNumber = $this->generateReceiptNumber();


            $createdPayments = [];
            $totalAmount = 0;



            foreach ($validated['items'] as $item) {


                $fee = DB::table('frais')
                    ->where('id', $item['fee_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$fee) {
                    abort(
                        422,
                        "Fee {$item['fee_id']} does not exist."
                    );
                }



                $applicable = $this->feeIsApplicable(
                    $fee,
                    $student,
                    $inscription
                );

                if (!$applicable) {
                    abort(
                        422,
                        "Fee {$item['fee_id']} is not applicable to this student."
                    );
                }


                /*
                 * Vérifier la devise.
                 */
                if ($fee->devise !== $validated['currency']) {
                    abort(
                        422,
                        'The payment currency does not match the fee currency.'
                    );
                }


                /*
                 * Calcul du solde restant côté serveur.
                 */
                $outstandingBalance = $this->calculateOutstandingBalance(
                    $student,
                    $fee,
                    $inscription
                );


                $requestedAmount = (float) $item['amount'];


                /*
                 * Le montant final est contrôlé côté backend.
                 */
                if ($requestedAmount > $outstandingBalance) {
                    abort(
                        422,
                        "Payment amount exceeds the outstanding balance for fee {$item['fee_id']}."
                    );
                }


                /*
                 * Le backend peut également recalculer les valeurs
                 * financières au lieu de faire confiance au POS.
                 */
                $amount = $requestedAmount;

                $totalAmount += $amount;


                /*
                 * Création de la perception.
                 */
                $payment = Perception::create([

                    'reference' => $receiptNumber,

                    'user_id' => $user->id,

                    'frais_id' => $fee->id,

                    'inscription_id' => $inscription->id,

                    'annee_id' => $inscription->annee_id,

                    'custom_property' => [
                        'client_payment_id' => $validated['client_payment_id'],
                        'device_id' => $validated['device_id'],
                        'student_id' => $student->id,
                        'payment_method' => $validated['payment_method'],
                    ],

                    'montant' => $amount,

                    'taux' => $fee->taux ?? 1,

                    'devise' => $validated['currency'],

                    'frais_montant' => $fee->montant,

                    'paid_by' => $student->id,

                    /*
                     * Date générée par le backend.
                     */
                    'paid_at' => $paidAt,

                    'due_date' => $fee->due_date ?? null,
                ]);

                $createdPayments[] = $payment;
            }


            /*
             * 8. Retour API.
             *
             * Ici on retourne le premier paiement si ton
             * endpoint représente une perception unique.
             */
            $payment = $createdPayments[0];

            return response()->json([
                'data' => [
                    'id' => $payment->id,

                    'client_payment_id' =>
                        $validated['client_payment_id'],

                    'receipt_number' =>
                        $receiptNumber,

                    'status' => 'completed',

                    'student' => [
                        'id' => $student->id,
                        'name' => $student->name,
                    ],

                    'amount' => $totalAmount,

                    'currency' => $validated['currency'],

                    'payment_method' =>
                        $validated['payment_method'],

                    /*
                     * Date générée par le serveur.
                     */
                    'paid_at' =>
                        $paidAt->toIso8601String(),
                ],
            ], 201);
        });
    }


    private function generateReceiptNumber(): string
    {
        do {
            $number =
                'REC-' .
                now()->format('Y') .
                '-' .
                str_pad(
                    (string) random_int(1, 9999999),
                    7,
                    '0',
                    STR_PAD_LEFT
                );

        } while (
            Perception::where('reference', $number)->exists()
        );

        return $number;
    }


    private function feeIsApplicable(
        $fee,
        $student,
        $inscription
    ): bool {
        /*
         * À adapter à tes règles métier.
         *
         * Exemple :
         *
         * return $fee->annee_id === $inscription->annee_id;
         */

        return true;
    }


    private function calculateOutstandingBalance(
        $student,
        $fee,
        $inscription
    ): float {
        /*
         * À remplacer par ton vrai calcul financier.
         *
         * Exemple conceptuel :
         *
         * frais total
         * - paiements déjà effectués
         * = solde restant
         */

        $alreadyPaid = Perception::query()
            ->where('frais_id', $fee->id)
            ->where('inscription_id', $inscription->id)
            ->sum('montant');

        $balance = (float) $fee->montant - (float) $alreadyPaid;

        return max(0, $balance);
    }


    private function paymentResponse(Perception $payment)
    {
        return [
            'id' => $payment->id,

            'client_payment_id' =>
                data_get(
                    $payment->custom_property,
                    'client_payment_id'
                ),

            'receipt_number' =>
                $payment->reference,

            'status' => 'completed',

            'student' => [
                'id' => $payment->paid_by,
            ],

            'amount' =>
                (float) $payment->montant,

            'currency' =>
                $payment->devise,

            'paid_at' =>
                optional($payment->paid_at)->toIso8601String(),
        ];
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
