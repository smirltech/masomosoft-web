<?php

namespace App\Http\Controllers\Api\V1\Pos;

use App\Http\Controllers\Controller;
use App\Models\Annee;
use App\Models\Eleve;
use App\Models\Frais;
use App\Models\Perception;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PosStudentController extends Controller
{
    /**
     * ============================================================
     * STUDENT SEARCH
     * ============================================================
     *
     * GET /api/v1/pos/students?search=keyword&limit=10
     *
     * Search a student by:
     * - matricule
     * - name
     *
     * Returns a maximum of 10 students.
     */
    public function index(Request $request): JsonResponse
    {
        $search = trim(
            (string) $request->input('search', '')
        );

        /*
         * Maximum 10 results.
         */
        $limit = min(
            max(
                (int) $request->input('limit', 10),
                1
            ),
            10
        );

        /*
         * Empty search.
         */
        if ($search === '') {
            return response()->json([
                'data' => [],
            ]);
        }

        /*
         * Current academic year.
         */
        $anneeId = Annee::id();

        /*
         * Search students.
         */
        $students = Eleve::query()
            ->where(function ($query) use ($search) {

                $query
                    ->where(
                        'matricule',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'nom',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'numero_permanent',
                        'like',
                        '%' . $search . '%'
                    );

            })
            ->with([
                'inscriptions' => function ($query) use ($anneeId) {

                    $query
                        ->where('annee_id', $anneeId)
                        ->with('classe');

                },
            ])
            ->limit($limit)
            ->get();

        /*
         * Format response.
         */
        return response()->json([
            'data' => $students
                ->map(function (Eleve $student) {

                    $inscription =
                        $student->inscriptions->first();

                    return [
                        'id' => $student->id,

                        'name' => $this->studentName(
                            $student
                        ),

                        'matricule' =>
                            $student->matricule,

                        'class' =>
                            $inscription?->classe?->code,
                    ];

                })
                ->values(),
        ]);
    }


    /**
     * ============================================================
     * STUDENT LOOKUP
     * ============================================================
     *
     * GET /api/v1/pos/students/{identifier}
     *
     * Locate a student using:
     * - matricule
     * - numero_permanent
     * - database ID
     *
     * Returns:
     * - student
     * - financial summary
     * - unpaid / partially paid fees only
     */
    public function show(string $identifier): JsonResponse
    {
        $identifier = trim($identifier);

        /*
         * Validate identifier.
         */
        if ($identifier === '') {
            return response()->json([
                'message' =>
                    'Student identifier is required.',
            ], 422);
        }

        /*
         * Find student.
         */
        $student = $this->findStudent(
            $identifier
        );

        if (! $student) {
            return response()->json([
                'message' => 'Student not found.',
                'identifier' => $identifier,
            ], 404);
        }

        /*
         * Current academic year.
         */
        $anneeId = Annee::id();

        /*
         * Current inscription.
         */
        $inscription = $student
            ->inscriptions()
            ->with('classe')
            ->where('annee_id', $anneeId)
            ->first();

        /*
         * Student not registered.
         */
        if (! $inscription) {
            return response()->json([
                'message' =>
                    'Student is not registered for the current academic year.',

                'student' => [
                    'id' => $student->id,

                    'name' =>
                        $this->studentName($student),

                    'matricule' =>
                        $student->matricule,

                    'class' => null,
                ],

                'annee_id' => $anneeId,
            ], 422);
        }

        /*
         * Get fees.
         */
        $fees = Frais::query()
            ->where(function ($query) use ($anneeId) {

                $query
                    ->where(
                        'annee_id',
                        $anneeId
                    )
                    ->orWhereNull('annee_id');

            })
            ->orderBy('nom')
            ->get();

        /*
         * Get student payments.
         */
        $perceptions = Perception::query()
            ->where(
                'inscription_id',
                $inscription->id
            )
            ->where(
                'annee_id',
                $anneeId
            )
            ->where(
                'montant',
                '>',
                0
            )
            ->get();

        /*
         * Amount paid grouped by fee.
         */
        $paidByFee = $perceptions
            ->groupBy('frais_id')
            ->map(function ($items) {

                return $items->sum(
                    function ($perception) {

                        return (float)
                        $perception->montant;

                    }
                );
            });

        /*
         * Currency totals.
         */
        $totalDueUSD = 0;
        $totalPaidUSD = 0;

        $totalDueCDF = 0;
        $totalPaidCDF = 0;

        $feesData = [];

        /*
         * Build unpaid / partial fees.
         */
        foreach ($fees as $fee) {

            $amountDue =
                (float) $fee->montant;

            $currency =
                $this->enumValue(
                    $fee->devise
                );

            $currency =
                $currency ?: 'USD';

            $amountPaid =
                (float) (
                    $paidByFee->get(
                        $fee->id
                    ) ?? 0
                );

            $outstanding = max(
                $amountDue - $amountPaid,
                0
            );

            /*
             * Completely paid fees are excluded
             * from this endpoint.
             */
            if ($outstanding <= 0) {
                continue;
            }

            /*
             * Status.
             */
            if ($amountPaid <= 0) {

                $status = 'unpaid';

            } else {

                $status = 'partial';
            }

            /*
             * Currency totals.
             */
            if ($currency === 'USD') {

                $totalDueUSD +=
                    $amountDue;

                $totalPaidUSD +=
                    $amountPaid;

            } elseif ($currency === 'CDF') {

                $totalDueCDF +=
                    $amountDue;

                $totalPaidCDF +=
                    $amountPaid;
            }

            /*
             * Fee data.
             */
            $feesData[] = [

                'id' =>
                    $fee->id,

                'name' =>
                    $fee->nom,

                'period' =>
                    $this->enumValue(
                        $fee->frequence
                    ),

                'amount_due' =>
                    $amountDue,

                'amount_paid' =>
                    $amountPaid,

                'outstanding' =>
                    $outstanding,

                'status' =>
                    $status,

                'currency' =>
                    $currency,
            ];
        }

        /*
         * Determine currencies.
         */
        $currencies = collect(
            $feesData
        )
            ->pluck('currency')
            ->unique()
            ->values();

        /*
         * One currency.
         */
        if ($currencies->count() === 1) {

            $currency =
                $currencies->first();

            if ($currency === 'CDF') {

                $amountDue =
                    $totalDueCDF;

                $amountPaid =
                    $totalPaidCDF;

            } else {

                $amountDue =
                    $totalDueUSD;

                $amountPaid =
                    $totalPaidUSD;
            }

            $financial = [

                'currency' =>
                    $currency,

                'amount_due' =>
                    $amountDue,

                'amount_paid' =>
                    $amountPaid,

                'outstanding' =>
                    max(
                        $amountDue -
                        $amountPaid,
                        0
                    ),
            ];

        } else {

            /*
             * Multiple currencies.
             */
            $financial = [

                'USD' => [

                    'amount_due' =>
                        $totalDueUSD,

                    'amount_paid' =>
                        $totalPaidUSD,

                    'outstanding' =>
                        max(
                            $totalDueUSD -
                            $totalPaidUSD,
                            0
                        ),
                ],

                'CDF' => [

                    'amount_due' =>
                        $totalDueCDF,

                    'amount_paid' =>
                        $totalPaidCDF,

                    'outstanding' =>
                        max(
                            $totalDueCDF -
                            $totalPaidCDF,
                            0
                        ),
                ],
            ];
        }

        /*
         * Final response.
         */
        return response()->json([

            'student' => [

                'id' =>
                    $student->id,

                'name' =>
                    $this->studentName(
                        $student
                    ),

                'matricule' =>
                    $student->matricule,

                'class' =>
                    $inscription
                        ->classe?->code,
            ],

            'financial' =>
                $financial,

            'fees' =>
                $feesData,
        ]);
    }


    /**
     * ============================================================
     * PAYMENT CONTEXT
     * ============================================================
     *
     * GET /api/v1/pos/students/{identifier}/payment-context
     *
     * Returns ALL fees:
     * - unpaid
     * - partial
     * - paid
     */
    public function paymentContext(
        string $identifier
    ): JsonResponse {

        /*
         * Find student.
         */
        $student = $this->findStudent(
            $identifier
        );

        if (! $student) {
            return response()->json([
                'message' =>
                    'Student not found.',
            ], 404);
        }

        /*
         * Current academic year.
         */
        $anneeId = Annee::id();

        /*
         * Current inscription.
         */
        $inscription = $student
            ->inscriptions()
            ->with('classe')
            ->where(
                'annee_id',
                $anneeId
            )
            ->first();

        /*
         * Student not registered.
         */
        if (! $inscription) {
            return response()->json([
                'message' =>
                    'Student is not registered for the current academic year.',

                'student' => [

                    'id' =>
                        $student->id,

                    'name' =>
                        $this->studentName(
                            $student
                        ),

                    'matricule' =>
                        $student->matricule,

                    'class' => null,
                ],

                'annee_id' =>
                    $anneeId,
            ], 422);
        }

        /*
         * Get all fees.
         */
        $fees = Frais::query()
            ->where(function ($query) use ($anneeId) {

                $query
                    ->where(
                        'annee_id',
                        $anneeId
                    )
                    ->orWhereNull(
                        'annee_id'
                    );

            })
            ->orderBy('nom')
            ->get();

        /*
         * Get all perceptions.
         */
        $perceptions = Perception::query()
            ->where(
                'inscription_id',
                $inscription->id
            )
            ->where(
                'annee_id',
                $anneeId
            )
            ->where(
                'montant',
                '>',
                0
            )
            ->get();

        /*
         * Paid amount grouped by fee.
         */
        $paidByFee = $perceptions
            ->groupBy('frais_id')
            ->map(function ($items) {

                return $items->sum(
                    function ($perception) {

                        return (float)
                        $perception->montant;

                    }
                );
            });

        /*
         * Currency totals.
         */
        $totalDueUSD = 0;
        $totalPaidUSD = 0;

        $totalDueCDF = 0;
        $totalPaidCDF = 0;

        $feesData = [];

        /*
         * Build ALL fees.
         */
        foreach ($fees as $fee) {

            $amountDue =
                (float) $fee->montant;

            $currency =
                $this->enumValue(
                    $fee->devise
                );

            $currency =
                $currency ?: 'USD';

            $amountPaid =
                (float) (
                    $paidByFee->get(
                        $fee->id
                    ) ?? 0
                );

            $outstanding = max(
                $amountDue -
                $amountPaid,
                0
            );

            /*
             * Status.
             */
            if ($amountPaid <= 0) {

                $status = 'unpaid';

            } elseif (
                $amountPaid < $amountDue
            ) {

                $status = 'partial';

            } else {

                $status = 'paid';
            }

            /*
             * Currency totals.
             */
            if ($currency === 'USD') {

                $totalDueUSD +=
                    $amountDue;

                $totalPaidUSD +=
                    $amountPaid;

            } elseif ($currency === 'CDF') {

                $totalDueCDF +=
                    $amountDue;

                $totalPaidCDF +=
                    $amountPaid;
            }

            /*
             * Fee.
             */
            $feesData[] = [

                'id' =>
                    $fee->id,

                'name' =>
                    $fee->nom,

                'period' =>
                    $this->enumValue(
                        $fee->frequence
                    ),

                'amount_due' =>
                    $amountDue,

                'amount_paid' =>
                    $amountPaid,

                'outstanding' =>
                    $outstanding,

                'status' =>
                    $status,

                'currency' =>
                    $currency,
            ];
        }

        /*
         * Determine currencies.
         */
        $currencies = collect(
            $feesData
        )
            ->pluck('currency')
            ->unique()
            ->values();

        /*
         * One currency.
         */
        if ($currencies->count() === 1) {

            $currency =
                $currencies->first();

            if ($currency === 'CDF') {

                $amountDue =
                    $totalDueCDF;

                $amountPaid =
                    $totalPaidCDF;

            } else {

                $amountDue =
                    $totalDueUSD;

                $amountPaid =
                    $totalPaidUSD;
            }

            $financial = [

                'currency' =>
                    $currency,

                'amount_due' =>
                    $amountDue,

                'amount_paid' =>
                    $amountPaid,

                'outstanding' =>
                    max(
                        $amountDue -
                        $amountPaid,
                        0
                    ),
            ];

        } else {

            /*
             * Multiple currencies.
             */
            $financial = [

                'USD' => [

                    'amount_due' =>
                        $totalDueUSD,

                    'amount_paid' =>
                        $totalPaidUSD,

                    'outstanding' =>
                        max(
                            $totalDueUSD -
                            $totalPaidUSD,
                            0
                        ),
                ],

                'CDF' => [

                    'amount_due' =>
                        $totalDueCDF,

                    'amount_paid' =>
                        $totalPaidCDF,

                    'outstanding' =>
                        max(
                            $totalDueCDF -
                            $totalPaidCDF,
                            0
                        ),
                ],
            ];
        }

        /*
         * Final response.
         */
        return response()->json([

            'student' => [

                'id' =>
                    $student->id,

                'name' =>
                    $this->studentName(
                        $student
                    ),

                'matricule' =>
                    $student->matricule,

                'class' =>
                    $inscription
                        ->classe?->code,
            ],

            'financial' =>
                $financial,

            'fees' =>
                $feesData,
        ]);
    }


    /**
     * ============================================================
     * FIND STUDENT
     * ============================================================
     *
     * Supported identifiers:
     * - matricule
     * - numero_permanent
     * - ID
     */
    private function findStudent(
        string $identifier
    ): ?Eleve {

        return Eleve::query()
            ->where(function ($query) use ($identifier) {

                $query
                    ->where(
                        'matricule',
                        $identifier
                    )
                    ->orWhere(
                        'numero_permanent',
                        $identifier
                    )
                    ->orWhere(
                        'id',
                        $identifier
                    );

            })
            ->first();
    }


    /**
     * ============================================================
     * STUDENT NAME
     * ============================================================
     */
    private function studentName(
        Eleve $student
    ): string {

        return trim(
            $student->nom
        );
    }


    /**
     * ============================================================
     * ENUM VALUE
     * ============================================================
     */
    private function enumValue(
        mixed $value
    ): mixed {

        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        return $value;
    }


    /**
     * ============================================================
     * FORMAT DATETIME
     * ============================================================
     */
    private function formatDateTime(
        mixed $value
    ): ?string {

        if (! $value) {
            return null;
        }

        if (
            $value instanceof
            \DateTimeInterface
        ) {

            return $value->format(
                'Y-m-d H:i:s'
            );
        }

        return Carbon::parse(
            $value
        )->format(
            'Y-m-d H:i:s'
        );
    }


    /**
     * ============================================================
     * FORMAT DATE
     * ============================================================
     */
    private function formatDate(
        mixed $value
    ): ?string {

        if (! $value) {
            return null;
        }

        if (
            $value instanceof
            \DateTimeInterface
        ) {

            return $value->format(
                'Y-m-d'
            );
        }

        return Carbon::parse(
            $value
        )->format(
            'Y-m-d'
        );
    }
}
