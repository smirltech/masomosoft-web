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
            ->with(['classe'])
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

        $fees = \App\Models\Frais::query()
            ->where('annee_id', $anneeId)
            ->get();

        $perceptions = Perception::query()
            ->where('inscription_id', $inscription->id)
            ->where('annee_id', $anneeId)
            ->with('frais')
            ->get();

        $feesData = [];

        $totalDue = 0;
        $totalPaid = 0;

        foreach ($fees as $fee) {

            $amountDue = (float) $fee->montant;

            $payments = $perceptions->where('frais_id', $fee->id);

            $amountPaid = (float) $payments->sum('montant');

            $outstanding = max(
                0,
                $amountDue - $amountPaid
            );

            $totalDue += $amountDue;
            $totalPaid += $amountPaid;

            $period = null;

            if ($fee->frequence !== null) {

                if (method_exists($fee->frequence, 'label')) {
                    $period = $fee->frequence->label();
                } elseif (isset($fee->frequence->value)) {
                    $period = $fee->frequence->value;
                } else {
                    $period = (string) $fee->frequence;
                }
            }

            $status = match (true) {
                $amountPaid <= 0 => 'unpaid',
                $outstanding <= 0 => 'paid',
                default => 'partial',
            };

            $feesData[] = [
                'id' => $fee->id,
                'name' => $fee->nom,
                'period' => $period,
                'amount_due' => $amountDue,
                'amount_paid' => $amountPaid,
                'outstanding' => $outstanding,
                'status' => $status,
                'currency' => $fee->devise?->value,
            ];
        }

        $outstanding = max(
            0,
            $totalDue - $totalPaid
        );

        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $this->studentName($student),
                'matricule' => $student->matricule,
                'class' => $inscription->classe?->code,
            ],

            'financial' => [
                'currency' => 'USD',
                'amount_due' => $totalDue,
                'amount_paid' => $totalPaid,
                'outstanding' => $outstanding,
            ],

            'fees' => $feesData,
        ]);
    }

    /**
     * GET /api/v1/pos/students/{identifier}/payments
     * Liste tous les frais déjà payés par l'élève (historique des perceptions).
     */
    public function payments(string $identifier): JsonResponse
    {
        $student = $this->findStudent($identifier);

        if (! $student) {
            return response()->json([
                'message' => 'Student not found.',
            ], 404);
        }

        $anneeId = Annee::id();

        $inscription = $student->inscriptions()
            ->where('annee_id', $anneeId)
            ->first();

        if (! $inscription) {
            return response()->json([
                'message' => 'Student is not registered for the current academic year.',
            ], 422);
        }

        $perceptions = Perception::query()
            ->where('inscription_id', $inscription->id)
            ->where('annee_id', $anneeId)
            ->with('frais')
            ->orderByDesc('paid_at')
            ->get();

        $data = $perceptions->map(function (Perception $p) {
            return [
                'id' => $p->id,
                'reference' => $p->reference,
                'fee_id' => $p->frais_id,
                'fee_name' => $p->frais?->nom,
                'amount_paid' => (float) $p->montant,
                'amount_due' => (float) $p->frais_montant,
                'currency' => $p->devise?->value,
                'paid_by' => $p->paid_by,
                'paid_at' => $p->paid_at,
            ];
        });

        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $this->studentName($student),
                'matricule' => $student->matricule,
            ],
            'total_paid' => (float) $perceptions->sum('montant'),
            'payments' => $data,
        ]);
    }

    private function findStudent(string $identifier): ?Eleve
    {
        return Eleve::query()
            ->where(function ($query) use ($identifier) {
                $query->where('matricule', $identifier)
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
