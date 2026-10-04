<?php

namespace App\Http\Controllers\Api\V1\Pos;

use App\Http\Controllers\Controller;
use App\Models\Annee;
use App\Models\Eleve;
use App\Models\Frais;
use App\Models\Perception;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class PosStudentController extends Controller
{
    public function show(string $identifier): JsonResponse
    {
        $student = $this->findStudent($identifier);

        if (! $student) {
            return response()->json([
                'message' => 'Student not found.',
            ], 404);
        }

        $inscription = $student->inscriptions()
            ->with('classe')
            ->where('annee_id', Annee::id())
            ->first();

        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $this->studentName($student),
                'matricule' => $student->matricule,
                'class' => $inscription?->classe?->code,
            ],
        ]);
    }

    public function paymentContext(string $identifier): JsonResponse
    {

        $student = $this->findStudent($identifier);

        if (! $student) {
            return response()->json([
                'message' => 'Student not found.',
            ], 404);
        }

        $anneeId = Annee::id();

        $inscription = $student->inscriptions()
            ->with('classe')
            ->where('annee_id', $anneeId)
            ->first();

        if (! $inscription) {
            return response()->json([
                'message' => 'Student is not registered for the current academic year.',

                'student' => [
                    'id' => $student->id,
                    'name' => $this->studentName($student),
                    'matricule' => $student->matricule,
                ],

                'annee_id' => $anneeId,
            ], 422);
        }

        $fees = Frais::query()
            ->where(function ($query) use ($anneeId) {
                $query
                    ->where('annee_id', $anneeId)
                    ->orWhereNull('annee_id');
            })
            ->orderBy('nom')
            ->get();

        $perceptions = Perception::query()
            ->where('inscription_id', $inscription->id)
            ->where('annee_id', $anneeId)
            ->where('montant', '>', 0)
            ->get();


        $paidByFee = $perceptions
            ->groupBy('frais_id')
            ->map(function ($items) {
                return $items->sum(function ($perception) {
                    return (float) $perception->montant;
                });
            });


        $totalDueUSD = 0;
        $totalPaidUSD = 0;

        $totalDueCDF = 0;
        $totalPaidCDF = 0;

        $feesData = [];


        foreach ($fees as $fee) {


            $amountDue = (float) $fee->montant;


            $currency = $this->enumValue($fee->devise);

            $currency = $currency ?: 'USD';


            $amountPaid = (float) ($paidByFee->get($fee->id) ?? 0);


            $outstanding = max(
                $amountDue - $amountPaid,
                0
            );



            if ($amountPaid <= 0) {

                $status = 'unpaid';

            } elseif ($amountPaid < $amountDue) {

                $status = 'partial';

            } else {

                $status = 'paid';
            }



            if ($currency === 'USD') {

                $totalDueUSD += $amountDue;

                $totalPaidUSD += $amountPaid;

            } elseif ($currency === 'CDF') {

                $totalDueCDF += $amountDue;

                $totalPaidCDF += $amountPaid;
            }

            $feesData[] = [

                'id' => $fee->id,

                'name' => $fee->nom,

                'period' => $this->enumValue(
                    $fee->frequence
                ),

                'amount_due' => $amountDue,

                'amount_paid' => $amountPaid,

                'outstanding' => $outstanding,

                'status' => $status,

                'currency' => $currency,
            ];
        }

        $currencies = collect($feesData)
            ->pluck('currency')
            ->unique()
            ->values();

        if ($currencies->count() === 1) {

            $currency = $currencies->first();

            if ($currency === 'CDF') {

                $amountDue = $totalDueCDF;
                $amountPaid = $totalPaidCDF;

            } else {

                $amountDue = $totalDueUSD;
                $amountPaid = $totalPaidUSD;
            }

            $financial = [
                'currency' => $currency,

                'amount_due' => $amountDue,

                'amount_paid' => $amountPaid,

                'outstanding' => max(
                    $amountDue - $amountPaid,
                    0
                ),
            ];

        } else {


            $financial = [
                'USD' => [
                    'amount_due' => $totalDueUSD,
                    'amount_paid' => $totalPaidUSD,
                    'outstanding' => max(
                        $totalDueUSD - $totalPaidUSD,
                        0
                    ),
                ],

                'CDF' => [
                    'amount_due' => $totalDueCDF,
                    'amount_paid' => $totalPaidCDF,
                    'outstanding' => max(
                        $totalDueCDF - $totalPaidCDF,
                        0
                    ),
                ],
            ];
        }

        return response()->json([

            'student' => [

                'id' => $student->id,

                'name' => $this->studentName($student),

                'matricule' => $student->matricule,

                'class' => $inscription->classe?->code,
            ],

            'financial' => $financial,

            'fees' => $feesData,
        ]);
    }

    private function findStudent(string $identifier): ?Eleve
    {
        return Eleve::query()
            ->where(function ($query) use ($identifier) {

                $query
                    ->where('matricule', $identifier)
                    ->orWhere('numero_permanent', $identifier)
                    ->orWhere('id', $identifier);

            })
            ->first();
    }

    private function studentName(Eleve $student): string
    {
        return trim($student->nom);
    }
    private function enumValue(mixed $value): mixed
    {
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        return $value;
    }

    private function formatDateTime(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    private function formatDate(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return Carbon::parse($value)->format('Y-m-d');
    }
}
