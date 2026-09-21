<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Enums\Devise;
use App\Http\Controllers\Controller;
use App\Models\Annee;
use App\Models\Frais;
use App\Models\Inscription;
use App\Models\Perception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function studentCountByClass(): JsonResponse
    {
        $annee = Annee::encours();

        $classes = Inscription::with('classe')
            ->where('annee_id', $annee->id)
            ->get()
            ->groupBy('classe_id');

        return response()->json([
            'data' => [
                'annee_id' => $annee->id,

                'total_eleves' => $classes->sum(
                    fn ($inscriptions) => $inscriptions->count()
                ),

                'classes' => $classes->map(function ($inscriptions) {
                    $classe = $inscriptions->first()->classe;

                    return [
                        'classe_id' => $classe?->id,
                        'classe' => $classe?->code,
                        'total_eleves' => $inscriptions->count(),
                    ];
                })->values(),
            ],
        ]);
    }
    public function byClass(): JsonResponse
    {
        $annee = Annee::encours();

        $classes = Inscription::with('classe')
            ->where('annee_id', $annee->id)

            ->get()
            ->groupBy('classe_id');

        return response()->json([
            'data' => $classes->map(function ($inscriptions) {
                $classe = $inscriptions->first()->classe;

                return [
                    'classe_id' => $classe->id,
                    'classe' => $classe->code,
                    'total_eleves' => $inscriptions->count(),
                ];
            })->values(),

        ]);
    }




    public function insolvables(Request $request): JsonResponse
    {
        $annee = Annee::encours();

        $sectionId = $request->input('section_id');
        $classeId = $request->input('classe_id');
        $fraisId = $request->input('frais_id');
        $month = $request->input('month');


        $frais = $fraisId
            ? Frais::find($fraisId)
            : null;

        $perceptionQuery = Perception::query()
            ->where('annee_id', $annee->id)

            ->when($sectionId, function ($query) use ($sectionId) {
                $query->whereHas('inscription.classe', function ($query) use ($sectionId) {
                    $query->where('section_id', $sectionId);
                });
            })

            ->when($classeId, function ($query) use ($classeId) {
                $query->whereHas('inscription', function ($query) use ($classeId) {
                    $query->where('classe_id', $classeId);
                });
            })

            ->when($fraisId, function ($query) use ($fraisId) {
                $query->where('frais_id', $fraisId);
            })

            ->when($month, function ($query) use ($month) {
                $query->where(
                    'custom_property',
                    'like',
                    '%' . $month . '%'
                );
            });

        $inscriptionIds = $perceptionQuery
            ->pluck('inscription_id')
            ->unique();



        $inscriptions = Inscription::with([
            'classe',
            'eleve',
        ])
            ->where('annee_id', $annee->id)

            ->when($sectionId, function ($query) use ($sectionId) {
                $query->whereHas('classe', function ($query) use ($sectionId) {
                    $query->where('section_id', $sectionId);
                });
            })

            ->when($classeId, function ($query) use ($classeId) {
                $query->where('classe_id', $classeId);
            })

            ->whereNotIn('id', $inscriptionIds)

            ->get();


        return response()->json([
            'data' => [
                'annee' => [
                    'id' => $annee->id,
                    'nom' => $annee->nom,
                ],

                'filters' => [
                    'section_id' => $sectionId,
                    'classe_id' => $classeId,
                    'frais_id' => $fraisId,
                    'month' => $month,
                ],

                'frais' => $frais
                    ? [
                        'id' => $frais->id,
                        'nom' => $frais->nom,
                        'montant' => (float) $frais->montant,
                        'devise' => $frais->devise?->value
                            ?? $frais->devise,
                    ]
                    : null,

                'total_insolvables' => $inscriptions->count(),

                'students' => $inscriptions->map(function ($inscription) {

                    $eleve = $inscription->eleve;

                    return [
                        'inscription_id' => $inscription->id,

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

                        'classe' => $inscription->classe
                            ? [
                                'id' => $inscription->classe->id,
                                'code' => $inscription->classe->code,
                            ]
                            : null,
                    ];
                })->values(),
            ],
        ]);
    }
}
