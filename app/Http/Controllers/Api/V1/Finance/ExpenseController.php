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
            ->when($request->filled('type_id'), fn ($q) => $q->where('depense_type_id', $request->input('type_id')))
            ->when($request->filled('status'), function ($q) use ($request) {

                $q->whereHas('statuses', function ($sub) use ($request) {
                    $sub->where('name', $request->input('status'))
                        ->whereIn('id', function ($inner) {
                            $inner->selectRaw('MAX(id)')
                                ->from('statuses')
                                ->where('model_type', Depense::class)
                                ->groupBy('model_id');
                        });
                });
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($sub) use ($search) {
                    $sub->where('reference', 'like', "%{$search}%")
                        ->orWhere('motif', 'like', "%{$search}%")
                        ->orWhere('beneficiaire', 'like', "%{$search}%");
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


    public function show(string $id): JsonResponse
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
            'reference' => $depense->reference,
            'description' => $depense->motif,
            'category' => $depense->categorie?->value,
            'type' => $depense->type?->nom,
            'beneficiary' => $depense->beneficiaire,
            'amount' => (float) $depense->montant,
            'currency' => $depense->devise?->value,
            'date' => optional($depense->date)->format('Y-m-d'),
            'status' => $depense->status(),
            'note' => $depense->note,
            'validated_at' => optional($depense->validated_at)->toDateTimeString(),
            'created_by' => $depense->user?->name,
            'created_at' => optional($depense->created_at)->toDateTimeString(),
        ];
    }
}
