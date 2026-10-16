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
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
            'date_debut' => ['nullable', 'date'],
            'date_fin'   => ['nullable', 'date', 'after_or_equal:date_debut'],
            'currency'   => ['nullable', 'in:USD,CDF'],
            'type_id'    => ['nullable', 'integer'],
            'status'     => ['nullable', 'string'],
            'search'     => ['nullable', 'string', 'max:255'],
            'per_page'   => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);


        $dateDebut = $request->input('start_date', $request->input('date_debut'));
        $dateFin   = $request->input('end_date', $request->input('date_fin'));

        if (! $dateDebut || ! $dateFin) {
            $annee     = Annee::encours();
            $dateDebut = $dateDebut ?? $annee?->date_debut;
            $dateFin   = $dateFin ?? $annee?->date_fin;
        }

        $baseQuery = Depense::query()
            ->when($dateDebut && $dateFin, fn ($q) => $q->whereBetween('date', [$dateDebut, $dateFin]))
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
            ->selectRaw('devise, COALESCE(SUM(montant), 0) as total')
            ->groupBy('devise')
            ->pluck('total', 'devise');

        $depenses = (clone $baseQuery)
            ->when($request->filled('currency'), fn ($q) => $q->where('devise', $request->input('currency')))
            ->with(['type', 'user'])
            ->latest('date')
            ->paginate((int) $request->input('per_page', 20))
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
                'per_page'     => $depenses->perPage(),
                'total'        => $depenses->total(),
                'last_page'    => $depenses->lastPage(),
                'has_more'     => $depenses->hasMorePages(),
            ],
            'period' => [
                'start_date' => $dateDebut,
                'end_date'   => $dateFin,
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $depense = Depense::with(['type', 'user'])->findOrFail($id);

        return response()->json([
            'data' => $this->transform($depense),
        ]);
    }

    private function transform(Depense $depense): array
    {
        return [
            'id'           => $depense->id,
            'reference'    => $depense->reference,
            'description'  => $depense->motif,
            'category'     => $depense->categorie?->value,
            'type'         => $depense->type?->nom,
            'beneficiary'  => $depense->beneficiaire,
            'amount'       => (float) $depense->montant,
            'currency'     => $depense->devise?->value,
            'date'         => optional($depense->date)->format('Y-m-d'),
            'status'       => $depense->status(),
            'note'         => $depense->note,
            'validated_at' => optional($depense->validated_at)->toIso8601String(),
            'created_by'   => $depense->user?->name,
            'created_at'   => optional($depense->created_at)->toIso8601String(),
        ];
    }
}
