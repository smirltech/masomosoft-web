<?php

namespace App\Http\Controllers\Api\V1\Pos;

use App\Http\Controllers\Controller;
use App\Models\Annee;
use App\Models\Eleve;
use App\Models\Perception;
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

        $perceptions = Perception::query()
            ->where('inscription_id', $inscription->id)
            ->where('annee_id', $anneeId)
            ->where('montant', '>', 0)
            ->with('frais')
            ->orderByDesc('paid_at')
            ->orderByDesc('created_at')
            ->get();

        $paymentsData = [];

        $totalPaidUSD = 0;
        $totalPaidCDF = 0;

        foreach ($perceptions as $perception) {

            $amountPaid = (float) $perception->montant;

            $currency = $perception->devise;

            if ($currency instanceof \BackedEnum) {
                $currency = $currency->value;
            }

            $currency = $currency ?: $perception->frais?->devise;

            if ($currency instanceof \BackedEnum) {
                $currency = $currency->value;
            }

            $currency = $currency ?: 'USD';

            if ($currency === 'USD') {
                $totalPaidUSD += $amountPaid;
            }

            if ($currency === 'CDF') {
                $totalPaidCDF += $amountPaid;
            }


            $paymentsData[] = [
                'id' => $perception->id,
                'reference' => $perception->reference,

                'fee' => [
                    'id' => $perception->frais?->id,
                    'name' => $perception->frais?->nom,
                    'type' => $perception->frais?->type?->value,
                ],

                'amount' => $amountPaid,

                'currency' => $currency,

                'paid_by' => $perception->paid_by,

                'paid_at' => $perception->paid_at?->format('Y-m-d H:i:s'),

                'due_date' => $perception->due_date?->format('Y-m-d'),

                'created_at' => $perception->created_at?->format('Y-m-d H:i:s'),
            ];
        }


        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $this->studentName($student),
                'matricule' => $student->matricule,
                'class' => $inscription->classe?->code,
            ],

            'financial' => [
                'USD' => [
                    'amount_paid' => $totalPaidUSD,
                ],

                'CDF' => [
                    'amount_paid' => $totalPaidCDF,
                ],

                'total_payments' => $perceptions->count(),
            ],

            'payments' => $paymentsData,
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
}
