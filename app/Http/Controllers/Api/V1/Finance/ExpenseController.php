<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Controller;
use App\Models\Annee;
use App\Models\Depense;
use Illuminate\Http\JsonResponse;

class ExpenseController extends Controller
{
    public function index(): JsonResponse
    {
        $annee = Annee::encours();

        $depenses = Depense::with('depenseType')
            ->whereBetween('date', [$annee->date_debut, $annee->date_fin])
            ->latest('date')
            ->get();

        return response()->json([
            'data' => $depenses,
        ]);
    }
}
