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
            ->where('annee_id', $annee->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $depenses,
        ]);
    }
}
