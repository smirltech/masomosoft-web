<?php

namespace App\Http\Controllers\Api\V1\Scolarite;

use App\Http\Controllers\Controller;
use App\Models\Eleve;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EleveController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $eleves = Eleve::query()

            ->with([
                'inscriptions.classe',
            ])
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'data' => $eleves,
        ]);
    }
}
