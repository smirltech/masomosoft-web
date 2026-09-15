<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Enums\Devise;
use App\Http\Controllers\Controller;
use App\Models\Annee;
use App\Models\Eleve;
use App\Models\Perception;
use Illuminate\Http\JsonResponse;

class PerceptionController extends Controller
{
    /**
     * Liste des perceptions
     */
    public function index(): JsonResponse
    {
        $annee = Annee::encours();

        $perceptions = Perception::with([
            'frais',
            'inscription.classe',
        ])
            ->where('annee_id', $annee->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $perceptions->map(function (Perception $perception) {

                $eleve = null;

                if ($perception->inscription?->eleve_id) {
                    $eleve = Eleve::find(
                        $perception->inscription->eleve_id
                    );
                }

                return [
                    'id' => $perception->id,
                    'reference' => $perception->reference,
                    'montant' => (float) $perception->montant,

                    'devise' => $perception->devise?->value
                        ?? $perception->devise,

                    'due_date' => $perception->due_date,

                    'created_at' => $perception->created_at?->toISOString(),

                    'eleve' => $eleve
                        ? [
                            'id' => $eleve->id,
                            'matricule' => $eleve->matricule,
                            'nom' => $eleve->nom,
                            'sexe' => $eleve->sexe,
                            'lieu_naissance' => $eleve->lieu_naissance,
                            'date_naissance' => $eleve->date_naissance,
                            'adresse' => $eleve->adresse,
                            'email' => $eleve->email,
                            'telephone' => $eleve->telephone,
                            'numero_permanent' => $eleve->numero_permanent,
                        ]
                        : null,

                    'classe' => $perception->inscription?->classe
                        ? [
                            'id' => $perception->inscription->classe->id,
                            'code' => $perception->inscription->classe->code,
                        ]
                        : null,

                    'frais' => $perception->frais
                        ? [
                            'id' => $perception->frais->id,
                            'nom' => $perception->frais->nom,
                            'montant' => (float) $perception->frais->montant,
                            'devise' => $perception->frais->devise?->value
                                ?? $perception->frais->devise,
                        ]
                        : null,
                ];
            }),
        ]);
    }

    /**
     * Total des perceptions par frais
     */
    public function byFee(): JsonResponse
    {
        $annee = Annee::encours();

        $perceptions = Perception::with('frais')
            ->where('annee_id', $annee->id)
            ->get()
            ->groupBy('frais_id');

        return response()->json([
            'data' => $perceptions->map(function ($items) {

                $frais = $items->first()->frais;

                return [
                    'frais_id' => $frais?->id,

                    'frais' => $frais?->nom,

                    'devise' => $frais?->devise?->value
                        ?? $frais?->devise,

                    'total' => (float) $items->sum('montant'),

                    'nombre_paiements' => $items->count(),
                ];

            })->values(),
        ]);
    }


    /**
     * Total des perceptions en USD et CDF
     */
    public function total(): JsonResponse
    {
        $annee = Annee::encours();

        $usd = Perception::where('annee_id', $annee->id)
            ->where('devise', Devise::USD)
            ->sum('montant');

        $cdf = Perception::where('annee_id', $annee->id)
            ->where('devise', Devise::CDF)
            ->sum('montant');

        return response()->json([
            'data' => [
                'annee_id' => $annee->id,
                'usd' => (float) $usd,
                'cdf' => (float) $cdf,
            ],
        ]);
    }
}
