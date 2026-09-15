<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Enums\Devise;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Finance\RevenueIndexRequest;
use App\Models\Annee;
use App\Models\Perception;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class RevenueController extends Controller
{
    public function index(RevenueIndexRequest $request): JsonResponse
    {
        $query = Perception::query()->whereNotNull('paid_at');
        $annee = Annee::encours();
        $query->when($annee, fn ($query) => $query->where('annee_id', $annee->id));

        if ($request->filled('startDate')) {
            $query->whereBetween('paid_at', [
                Carbon::parse($request->string('startDate'))->startOfDay(),
                Carbon::parse($request->string('endDate'))->endOfDay(),
            ]);
        } elseif ($request->filled('period')) {
            $query->whereBetween('paid_at', match ($request->string('period')->toString()) {
                'day' => [now()->startOfDay(), now()->endOfDay()],
                'month' => [now()->startOfMonth(), now()->endOfMonth()],
                'year' => [now()->startOfYear(), now()->endOfYear()],
            });
        }

        $totals = (clone $query)->selectRaw('devise, COALESCE(SUM(montant), 0) as total')
            ->groupBy('devise')->pluck('total', 'devise');

        $revenues = $query->latest('paid_at')
            ->paginate((int) $request->get('per_page', 20));

        return response()->json([
            'data' => [
                Devise::USD->value => (float) ($totals[Devise::USD->value] ?? 0),
                Devise::CDF->value => (float) ($totals[Devise::CDF->value] ?? 0),
            ],
            'revenues' => $revenues->items(),
            'pagination' => [
                'current_page' => $revenues->currentPage(),
                'per_page' => $revenues->perPage(),
                'total' => $revenues->total(),
                'last_page' => $revenues->lastPage(),
            ],
        ]);
    }
}
