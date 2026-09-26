<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Controller;
use App\Models\Annee;
use App\Models\Depense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');

        if (!$dateDebut || !$dateFin) {
            $annee = Annee::encours();
            $dateDebut = $dateDebut ?? $annee?->date_debut;
            $dateFin = $dateFin ?? $annee?->date_fin;
        }

        $depenses = Depense::with('type')
            ->when($dateDebut && $dateFin, function ($query) use ($dateDebut, $dateFin) {
                $query->whereBetween('date', [$dateDebut, $dateFin]);
            })
            ->latest('date')
            ->get();

        return response()->json([
            'data' => [
                'periode' => [
                    'date_debut' => $dateDebut,
                    'date_fin' => $dateFin,
                ],
                'total' => $depenses->count(),
                'depenses' => $depenses,
            ],
        ]);
    }
}
