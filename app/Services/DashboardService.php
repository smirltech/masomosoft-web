<?php

namespace App\Services;

use App\Enums\Devise;
use App\Models\Annee;
use App\Models\Depense;
use App\Models\Perception;
use App\Models\Revenu;
use Carbon\Carbon;
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


    private function otherIncome(
        Devise $devise,
        int $anneeId
    ): float {
        return (float) Revenu::query()
            ->where('annee_id', $anneeId)
            ->where('devise', $devise)
            ->sum('montant');
    }


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

                    'currency' => $this->currencyValue(
                        $expense->devise
                    ),

                    'date' => $this->formatDate(
                        $expense->date
                        ?? $expense->created_at
                    ),
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

                    'currency' => $this->currencyValue(
                        $revenu->devise
                    ),

                    'date' => $this->formatDate(
                        $revenu->created_at
                    ),
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

                    'currency' => $this->currencyValue(
                        $perception->devise
                    ),

                    'date' => $this->formatDate(
                        $perception->paid_at
                    ),
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


    private function currencyValue($currency): ?string
    {
        if ($currency instanceof Devise) {
            return $currency->value;
        }

        if ($currency === null) {
            return null;
        }

        return strtoupper(trim((string) $currency));
    }


    private function formatDate($date): ?string
    {
        if ($date === null || $date === '') {
            return null;
        }

        if ($date instanceof Carbon) {
            return $date->toDateString();
        }

        if ($date instanceof \DateTimeInterface) {
            return $date->format('Y-m-d');
        }

        try {
            return Carbon::parse($date)->toDateString();
        } catch (\Throwable) {
            return (string) $date;
        }
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
        return Auth::check()
            ? Auth::user()->unreadNotifications()->count()
            : 0;
    }
}
