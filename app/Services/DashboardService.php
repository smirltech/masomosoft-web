<?php

namespace App\Services;

use App\Enums\Devise;
use App\Models\Annee;
use App\Models\Depense;
use App\Models\Perception;
use App\Models\Revenu;

class DashboardService
{
    public function getDashboard(): array
    {
        $anneeId = Annee::id();

        return [
            'financial_summary' => [
                'USD' => $this->financialSummary(
                    Devise::USD,
                    $anneeId
                ),

                'CDF' => $this->financialSummary(
                    Devise::CDF,
                    $anneeId
                ),
            ],

            'recent_transactions' => $this->recentTransactions($anneeId),

            'academic_year' => $this->academicYear($anneeId),

            'notifications_count' => 0,
        ];
    }

    private function financialSummary(
        Devise $devise,
        int $anneeId
    ): array {
        $income = $this->schoolFeeIncome($devise, $anneeId)
            + $this->otherIncome($devise, $anneeId);

        $expenses = $this->expenses($devise, $anneeId);

        return [
            'income' => $income,
            'expenses' => $expenses,
            'balance' => $income - $expenses,
        ];
    }

    /**
     * Total school-fee collections.
     */
  private function schoolFeeIncome(
    Devise $devise,
    int $anneeId
): float {
    return (float) Perception::query()
        ->where('annee_id', $anneeId)
        ->where('devise', $devise)
        ->paid()
        ->sum('montant');
}

    /**
     * Other income recorded through the revenues module.
     */
private function otherIncome(
    Devise $devise,
    int $anneeId
): float {
    return (float) Revenu::query()
        ->where('annee_id', $anneeId)
        ->where('devise', $devise)
        ->sum('montant');
}

    /**
     * Paid/validated expenses.
     */
  private function expenses(
    Devise $devise,
    int $anneeId
): float {
    return (float) Depense::query()
        ->where('annee_id', $anneeId)
        ->where('devise', $devise)
        ->whereNotNull('validated_at')
        ->sum('montant');
}

    /**
     * Recent financial transactions.
     */
    private function recentTransactions(int $anneeId): array
    {
        $expenses = Depense::query()
            ->forAnnee($anneeId)
            ->where('devise', Devise::USD)
            ->whereNotNull('validated_at')
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->map(function (Depense $expense) {
                return [
                    'id' => $expense->id,
                    'type' => 'expense',
                    'description' => $expense->motif
                        ?? $expense->beneficiaire
                        ?? 'Dépense',
                    'amount' => (float) $expense->montant,
                    'currency' => $expense->devise,
                    'date' => $expense->date
                        ?? $expense->created_at?->toDateString(),
                ];
            });

        $revenus = Revenu::query()
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->map(function (Revenu $revenu) {
                return [
                    'id' => $revenu->id,
                    'type' => 'income',
                    'description' => $revenu->nom,
                    'amount' => (float) $revenu->montant,
                    'currency' => $revenu->devise,
                    'date' => $revenu->created_at?->toDateString(),
                ];
            });

        $perceptions = Perception::query()
            ->whereNotNull('paid_at')
            ->latest('paid_at')
            ->limit(10)
            ->get()
            ->map(function (Perception $perception) {
                return [
                    'id' => $perception->id,
                    'type' => 'income',
                    'description' => 'Frais scolaires',
                    'amount' => (float) $perception->montant,
                    'currency' => $perception->devise,
                    'date' => $perception->paid_at?->toISOString(),
                ];
            });

        return $expenses
            ->concat($revenus)
            ->concat($perceptions)
            ->sortByDesc('date')
            ->take(10)
            ->values()
            ->all();
    }

    private function academicYear(?int $anneeId): ?array
    {
        if (! $anneeId) {
            return null;
        }

        $annee = Annee::find($anneeId);

        if (! $annee) {
            return null;
        }

        return [
            'id' => $annee->id,
            'name' => $annee->name,
            'is_current' => true,
        ];
    }
}
