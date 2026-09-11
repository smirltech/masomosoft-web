<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Enums\Devise;
use App\Http\Controllers\Controller;
use App\Models\Annee;
use App\Models\Inscription;
use App\Models\Perception;
use Illuminate\Http\JsonResponse;

class StudentController extends Controller
{
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
}
