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

        $baseQuery = Depense::query()
            ->when($dateDebut && $dateFin, function ($query) use ($dateDebut, $dateFin) {
                $query->whereBetween('date', [$dateDebut, $dateFin]);
            });


        $baseQuery
            ->when($request->filled('type_id'), fn ($q) => $q->where('type_id', $request->input('type_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('statut', $request->input('status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($sub) use ($search) {
                    $sub->where('motif', 'like', "%{$search}%")
                        ->orWhere('beneficiaire', 'like', "%{$search}%")
                        ->orWhere('reference', 'like', "%{$search}%");
                });
            });


        $totaux = (clone $baseQuery)
            ->selectRaw('devise, SUM(montant) as total')
            ->groupBy('devise')
            ->pluck('total', 'devise');

        $perPage = (int) $request->input('per_page', 15);

        $depenses = (clone $baseQuery)
            ->with('type')
            ->latest('date')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'summary' => [
                'USD' => (float) ($totaux['USD'] ?? 0),
                'CDF' => (float) ($totaux['CDF'] ?? 0),
            ],
            'data' => $depenses->getCollection()
                ->map(fn (Depense $depense) => $this->transform($depense))
                ->values(),
            'pagination' => [
                'current_page' => $depenses->currentPage(),
                'per_page' => $depenses->perPage(),
                'total' => $depenses->total(),
                'last_page' => $depenses->lastPage(),
                'from' => $depenses->firstItem(),
                'to' => $depenses->lastItem(),
            ],
            'periode' => [
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
            ],
        ]);
    }

    /**
     * GET /api/v1/finance/expenses/{id}
     */
    public function show(int $id): JsonResponse
    {
        $depense = Depense::with('type')->findOrFail($id);

        return response()->json([
            'data' => $this->transform($depense),
        ]);
    }


    private function transform(Depense $depense): array
    {
        return [
            'id' => $depense->id,
            'reference' => $depense->reference ?? (string) $depense->id,
            'description' => $depense->motif ?? $depense->description ?? null,
            'type' => $depense->type?->nom ?? $depense->type?->libelle ?? $depense->type_libelle ?? null,
            'beneficiary' => $depense->beneficiaire ?? $depense->beneficiary ?? null,
            'amount' => (float) $depense->montant,
            'currency' => $depense->devise ?? $depense->currency ?? null,
            'date' => $depense->date instanceof \Carbon\Carbon
                ? $depense->date->format('Y-m-d')
                : $depense->date,
            'status' => $depense->statut ?? $depense->status ?? null,
        ];
    }
}
