<?php

namespace App\Services;

use App\Enums\Devise;
use App\Models\Annee;
use App\Models\Depense;
use App\Models\Perception;
use App\Models\Revenu;
use Illuminate\Support\Facades\Auth;

class DashboardService
{
    public function getDashboard(): array
    {
        $anneeId = Annee::id();

        $usd = $this->financialSummary(Devise::USD, $anneeId);
        $cdf = $this->financialSummary(Devise::CDF, $anneeId);

        return [
            'balances' => [
                'USD' => $usd['balance'],
                'CDF' => $cdf['balance'],
            ],

            'recent_transactions' => $this->recentTransactions($anneeId),

            'statistics' => [
                'USD' => [
                    'income' => $usd['income'],
                    'expenses' => $usd['expenses'],
                ],
                'CDF' => [
                    'income' => $cdf['income'],
                    'expenses' => $cdf['expenses'],
                ],
                'academic_year' => $this->academicYear($anneeId),
            ],

            'notifications_count' => $this->notificationsCount(),
        ];
    }

    private function financialSummary(Devise $devise, int $anneeId): array
    {
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
    private function schoolFeeIncome(Devise $devise, int $anneeId): float
    {
        return (float) Perception::query()
            ->where('annee_id', $anneeId)
            ->where('devise', $devise)
            ->paid()
            ->sum('montant');
    }

    /**
     * Other income recorded through the revenues module.
     */
    private function otherIncome(Devise $devise, int $anneeId): float
    {
        return (float) Revenu::query()
            ->where('annee_id', $anneeId)
            ->where('devise', $devise)
            ->sum('montant');
    }

    /**
     * Paid/validated expenses.
     */
    private function expenses(Devise $devise, int $anneeId): float
    {
        return (float) Depense::query()
            ->where('annee_id', $anneeId)
            ->where('devise', $devise)
            ->whereNotNull('validated_at')
            ->sum('montant');
    }

    /**
     * Recent financial transactions, toutes devises confondues.
     */
    private function recentTransactions(int $anneeId): array
    {
        $expenses = Depense::query()
            ->forAnnee($anneeId)
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
                    'currency' => $expense->devise?->value,
                    'date' => $expense->date
                        ?? $expense->created_at?->toDateString(),
                ];
            });

        $revenus = Revenu::query()
            ->where('annee_id', $anneeId)
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->map(function (Revenu $revenu) {
                return [
                    'id' => $revenu->id,
                    'type' => 'income',
                    'description' => $revenu->nom,
                    'amount' => (float) $revenu->montant,
                    'currency' => $revenu->devise?->value,
                    'date' => $revenu->created_at?->toDateString(),
                ];
            });

        $perceptions = Perception::query()
            ->where('annee_id', $anneeId)
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
                    'currency' => $perception->devise?->value,
                    'date' => $perception->paid_at?->toDateString(),
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

    private function notificationsCount(): int
    {
        return Auth::check() ? Auth::user()->unreadNotifications()->count() : 0;
    }
}
