<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Annee;
use App\Models\Depense;
use App\Models\Revenu;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{

    public function financial(Request $request): JsonResponse
    {
        $currency = strtoupper($request->input('currency', 'USD'));

        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');

        if (!$dateDebut || !$dateFin) {
            $annee = Annee::encours();
            $dateDebut = $dateDebut ?? $annee?->date_debut;
            $dateFin = $dateFin ?? $annee?->date_fin;
        }

        $income = (float) Revenu::query()
            ->where('devise', $currency)
            ->when($dateDebut && $dateFin, fn ($q) => $q->whereBetween('created_at', [
                Carbon::parse($dateDebut)->startOfDay(),
                Carbon::parse($dateFin)->endOfDay(),
            ]))
            ->sum('montant');

        $expenses = (float) Depense::query()
            ->where('devise', $currency)
            ->when($dateDebut && $dateFin, fn ($q) => $q->whereBetween('date', [$dateDebut, $dateFin]))
            ->sum('montant');

        $balance = $income - $expenses;


        $chartEnd = $dateFin ? Carbon::parse($dateFin) : Carbon::now();
        $months = collect(range(5, 0))->map(fn ($i) => $chartEnd->copy()->subMonths($i)->startOfMonth());
        $chartStart = $months->first();
        $chartRangeEnd = $chartEnd->copy()->endOfMonth();

        $incomeByMonth = Revenu::query()
            ->where('devise', $currency)
            ->whereBetween('created_at', [$chartStart, $chartRangeEnd])
            ->get(['created_at', 'montant'])
            ->groupBy(fn ($row) => $row->created_at->format('Y-m'))
            ->map(fn ($rows) => (float) $rows->sum('montant'));

        $expensesByMonth = Depense::query()
            ->where('devise', $currency)
            ->whereBetween('date', [$chartStart->toDateString(), $chartRangeEnd->toDateString()])
            ->get(['date', 'montant'])
            ->groupBy(fn ($row) => Carbon::parse($row->date)->format('Y-m'))
            ->map(fn ($rows) => (float) $rows->sum('montant'));

        $chart = $months->map(function (Carbon $month) use ($incomeByMonth, $expensesByMonth) {
            $key = $month->format('Y-m');

            return [
                'period' => $key,
                'income' => (float) ($incomeByMonth[$key] ?? 0),
                'expenses' => (float) ($expensesByMonth[$key] ?? 0),
            ];
        })->values();

        return response()->json([
            'currency' => $currency,
            'period' => [
                'start' => $dateDebut,
                'end' => $dateFin,
            ],
            'summary' => [
                'income' => $income,
                'expenses' => $expenses,
                'balance' => $balance,
            ],
            'chart' => $chart,
            'insights' => $this->buildInsights($income, $expenses, $balance, $chart),
        ]);
    }


    private function buildInsights(float $income, float $expenses, float $balance, $chart): array
    {
        $insights = [];

        if ($balance < 0) {
            $insights[] = 'Les dépenses dépassent les revenus sur cette période.';
        } elseif ($income > 0 && $balance > 0) {
            $ratio = round(($balance / $income) * 100);
            $insights[] = "Solde positif représentant {$ratio}% des revenus sur la période.";
        }

        if ($chart->count() >= 2) {
            $last = $chart->last();
            $previous = $chart[$chart->count() - 2];

            if ($previous['expenses'] > 0) {
                $variation = round((($last['expenses'] - $previous['expenses']) / $previous['expenses']) * 100);
                if (abs($variation) >= 10) {
                    $direction = $variation > 0 ? 'en hausse' : 'en baisse';
                    $insights[] = "Les dépenses du dernier mois sont {$direction} de " . abs($variation) . "% par rapport au mois précédent.";
                }
            }

            if ($previous['income'] > 0) {
                $variation = round((($last['income'] - $previous['income']) / $previous['income']) * 100);
                if (abs($variation) >= 10) {
                    $direction = $variation > 0 ? 'en hausse' : 'en baisse';
                    $insights[] = "Les revenus du dernier mois sont {$direction} de " . abs($variation) . "% par rapport au mois précédent.";
                }
            }
        }

        $peakExpenseMonth = $chart->sortByDesc('expenses')->first();
        if ($peakExpenseMonth && $peakExpenseMonth['expenses'] > 0) {
            $insights[] = "Le mois avec le plus de dépenses est {$peakExpenseMonth['period']}.";
        }

        return $insights;
    }
}
