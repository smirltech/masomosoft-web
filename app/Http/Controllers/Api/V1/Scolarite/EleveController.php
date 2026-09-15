<?php

namespace App\Http\Controllers\Api\V1\Scolarite;

use App\Http\Controllers\Controller;
use App\Models\Eleve;
use App\Models\Annee;
use App\Enums\InscriptionStatus;
use App\Http\Resources\EleveResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EleveController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', $request->get('limit', 20));
        abort_if($perPage < 1 || $perPage > 100, 422, 'Le nombre d\'éléments par page doit être compris entre 1 et 100.');

        $annee = Annee::encours();

        $eleves = Eleve::query()
            ->with(['inscriptions' => fn ($query) => $query
                ->with('classe')
                ->when($annee, fn ($query) => $query->where('annee_id', $annee->id))
                ->where('status', InscriptionStatus::approved)])
            ->whereHas('inscriptions', fn ($query) => $query
                ->when($annee, fn ($query) => $query->where('annee_id', $annee->id))
                ->where('status', InscriptionStatus::approved))
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'data' => EleveResource::collection($eleves->getCollection()),
            'pagination' => [
                'current_page' => $eleves->currentPage(),
                'per_page' => $eleves->perPage(),
                'total' => $eleves->total(),
                'last_page' => $eleves->lastPage(),
            ],
        ]);
    }
}
