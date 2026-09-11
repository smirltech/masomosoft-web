<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Controller;
use App\Models\Perception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CaisseController extends Controller
{
    public function pay(Request $request, $id): JsonResponse
    {
        $request->validate([
            'paid_by' => ['nullable', 'integer'],
        ]);

        $perception = Perception::findOrFail($id);

        $perception->paid_by = $request->paid_by ?? auth()->id();
        $perception->paid_at = now();

        $perception->save();

        return response()->json([
            'message' => 'Facture payée avec succès',
            'data' => $perception,
        ]);
    }
}
