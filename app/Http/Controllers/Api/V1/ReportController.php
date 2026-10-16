<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Annee;
use App\Models\Depense;
use App\Models\Perception;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReportController extends Controller
{
    public function financial(Request $request): JsonResponse
    {
        $request->validate([
            'currency'   => ['nullable', 'in:USD,CDF,usd,cdf'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
            'date_debut' => ['nullable', 'date'],
            'date_fin'   => ['nullable', 'date', 'after_or_equal:date_debut'],
        ]);

        $currency = strtoupper($request->input('currency', 'USD'));


        $start = $request->input('start_date', $request->input('date_debut'));
        $end   = $request->input('end_date', $request->input('date_fin'));

        if (! $start || ! $end) {
            $annee = Annee::encours();
            $start = $start ?? $annee?->date_debut;
            $end   = $end ?? $annee?->date_fin;
        }

        $start = $start ? Carbon::parse($start)->startOfDay() : null;
        $end   = $end ? Carbon::parse($end)->endOfDay() : null;

        // Totaux de la période
        $income   = $this->incomeTotal($currency, $start, $end);
        $expenses = $this->expensesTotal($currency, $start, $end);
        $balance  = round($income - $expenses, 2);

        // Graphique : exactement 6 mois, jusqu'à la fin de période (plafonnée à aujourd'hui)
        $chartEnd = ($end && $end->lt(now())) ? $end->copy() : now();
        $lastMonth = $chartEnd->copy()->startOfMonth();

        $months = collect(range(5, 0))
            ->map(fn (int $i) => $lastMonth->copy()->subMonthsNoOverflow($i));

        $rangeStart = $months->first()->copy()->startOfMonth();
        $rangeEnd   = $lastMonth->copy()->endOfMonth();

        $incomeByMonth = Perception::query()
            ->whereNotNull('paid_at')
            ->where('devise', $currency)
            ->whereBetween('paid_at', [$rangeStart, $rangeEnd])
            ->get(['paid_at', 'montant'])
            ->groupBy(fn ($row) => $row->paid_at->format('Y-m'))
            ->map(fn ($rows) => (float) $rows->sum('montant'));

        $expensesByMonth = Depense::query()
            ->where('devise', $currency)
            ->whereBetween('date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
            ->get(['date', 'montant'])
            ->groupBy(fn ($row) => Carbon::parse($row->date)->format('Y-m'))
            ->map(fn ($rows) => (float) $rows->sum('montant'));

        $chart = $months->map(function (Carbon $month) use ($incomeByMonth, $expensesByMonth) {
            $key = $month->format('Y-m');

            return [
                'period'   => $key,
                'income'   => round((float) ($incomeByMonth[$key] ?? 0), 2),
                'expenses' => round((float) ($expensesByMonth[$key] ?? 0), 2),
            ];
        })->values();

        return response()->json([
            'currency' => $currency,
            'period'   => [
                'start' => $start?->toDateString(),
                'end'   => $end?->toDateString(),
            ],
            'summary'  => [
                'income'   => $income,
                'expenses' => $expenses,
                'balance'  => $balance,
            ],
            'chart'    => $chart,
            'insights' => $this->buildInsights($income, $expenses, $balance, $chart),
        ]);
    }

    private function incomeTotal(string $currency, ?Carbon $start, ?Carbon $end): float
    {
        return round((float) Perception::query()
            ->whereNotNull('paid_at')
            ->where('devise', $currency)
            ->when($start && $end, fn ($q) => $q->whereBetween('paid_at', [$start, $end]))
            ->sum('montant'), 2);
    }

    private function expensesTotal(string $currency, ?Carbon $start, ?Carbon $end): float
    {
        return round((float) Depense::query()
            ->where('devise', $currency)
            ->when($start && $end, fn ($q) => $q->whereBetween('date', [
                $start->toDateString(),
                $end->toDateString(),
            ]))
            ->sum('montant'), 2);
    }

    private function buildInsights(float $income, float $expenses, float $balance, Collection $chart): array
    {
        $insights = [];

        if ($balance < 0) {
            $insights[] = 'Les dépenses dépassent les revenus sur cette période.';
        } elseif ($income > 0 && $balance > 0) {
            $ratio = round(($balance / $income) * 100);
            $insights[] = "Solde positif représentant {$ratio}% des revenus sur la période.";
        }

        if ($chart->count() >= 2) {
            $last     = $chart->last();
            $previous = $chart[$chart->count() - 2];

            if ($previous['expenses'] > 0) {
                $variation = round((($last['expenses'] - $previous['expenses']) / $previous['expenses']) * 100);
                if (abs($variation) >= 10) {
                    $direction = $variation > 0 ? 'en hausse' : 'en baisse';
                    $insights[] = "Les dépenses du dernier mois sont {$direction} de " . abs($variation) . '% par rapport au mois précédent.';
                }
            }

            if ($previous['income'] > 0) {
                $variation = round((($last['income'] - $previous['income']) / $previous['income']) * 100);
                if (abs($variation) >= 10) {
                    $direction = $variation > 0 ? 'en hausse' : 'en baisse';
                    $insights[] = "Les revenus du dernier mois sont {$direction} de " . abs($variation) . '% par rapport au mois précédent.';
                }
            }
        }

        $peak = $chart->sortByDesc('expenses')->first();
        if ($peak && $peak['expenses'] > 0) {
            $insights[] = "Le mois avec le plus de dépenses est {$peak['period']}.";
        }

        return $insights;
    }
}
