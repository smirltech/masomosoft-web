<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Controller;
use App\Models\Inscription;
use App\Models\Perception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PerceptionClasseController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'annee_id' => ['required'],
            'classe_id' => ['required'],
            'fee_id' => ['required'],
            'due_date' => ['required', 'date'],
            'montant' => ['required', 'numeric'],
        ]);

        $inscriptions = Inscription::where([
            'classe_id' => $request->classe_id,
            'annee_id' => $request->annee_id,
        ])->get();

        foreach ($inscriptions as $inscription) {
            Perception::updateOrCreate(
                [
                    'frais_id' => $request->fee_id,
                    'inscription_id' => $inscription->id,
                    'annee_id' => $request->annee_id,
                    'due_date' => $request->due_date,
                ],
                [
                    'user_id' => auth()->id(),
                    'frais_id' => $request->fee_id,
                    'inscription_id' => $inscription->id,
                    'annee_id' => $request->annee_id,
                    'montant' => $request->montant,
                    'due_date' => $request->due_date,
                ]
            );
        }

        return response()->json([
            'message' => 'Classe facturée avec succès',
            'eleves_count' => $inscriptions->count(),
        ]);
    }
}
