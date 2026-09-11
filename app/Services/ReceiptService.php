<?php

namespace App\Services;

use App\Enums\Devise;
use App\Models\Annee;
use App\Models\Perception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ReceiptService
{
    /**
     * Get paginated receipts with summary and pagination metadata.
     */
    public function getReceipts(array $filters): array
    {
        $query = $this->baseQuery();

        $this->applyFilters($query, $filters);

        $receipts = $query
            ->latest('paid_at')
            ->paginate(
                $filters['per_page'] ?? 20
            );

        return [
            'summary' => $this->getSummary($filters),

            'items' => $receipts
                ->getCollection()
                ->map(fn (Perception $perception) =>
                    $this->transform($perception)
                )
                ->values()
                ->all(),

            'pagination' => $this->pagination($receipts),
        ];
    }

    /**
     * Get a single receipt.
     */
    public function getReceipt(int|string $id): array
    {
        $perception = $this->baseQuery()
            ->whereKey($id)
            ->first();

        if (!$perception) {
            abort(404, 'Receipt not found.');
        }

        return $this->transform($perception);
    }

    /**
     * Base query for paid perceptions belonging to
     * the current academic year.
     */
    private function baseQuery(): Builder
    {
        return Perception::query()
            ->with([
                'inscription.eleve',
                'inscription.classe',
                'frais',
            ])
            ->where('annee_id', Annee::id())
            ->paid();
    }

    /**
     * Apply supported mobile filters.
     */
    private function applyFilters(
        Builder $query,
        array $filters
    ): void {
        if (!empty($filters['date_from'])) {
            $query->whereDate(
                'paid_at',
                '>=',
                $filters['date_from']
            );
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate(
                'paid_at',
                '<=',
                $filters['date_to']
            );
        }

        if (!empty($filters['currency'])) {
            $query->where(
                'devise',
                $filters['currency']
            );
        }
    }

    /**
     * Financial summary for the selected filters.
     *
     * Summary is independent from pagination.
     */
    private function getSummary(array $filters): array
    {
        $query = $this->baseQuery();

        $this->applyFilters($query, $filters);

        return [
            'USD' => $this->currencyTotal(
                clone $query,
                Devise::USD
            ),

            'CDF' => $this->currencyTotal(
                clone $query,
                Devise::CDF
            ),
        ];
    }

    /**
     * Calculate total collected for a currency.
     */
    private function currencyTotal(
        Builder $query,
        Devise $currency
    ): float {
        return (float) $query
            ->where('devise', $currency)
            ->sum('montant');
    }

    /**
     * Transform a perception into the mobile API representation.
     */
    private function transform(
        Perception $perception
    ): array {
        $inscription = $perception->inscription;
        $eleve = $inscription?->eleve;
        $classe = $inscription?->classe;
        $frais = $perception->frais;

        $amount = (float) $perception->montant;
        $amountDue = (float) $perception->frais_montant;

        return [
            'id' => $perception->id,

            'student' => [
                'id' => $eleve?->id,
                'name' => $eleve?->fullName,
                'matricule' => $eleve?->matricule,
            ],

            'class' => [
                'id' => $classe?->id,
                'name' => $classe?->nom,
                'code' => $classe?->shortCode,
            ],

            'fee' => [
                'id' => $frais?->id,
                'name' => $frais?->nom,
            ],

            'amount' => $amount,

            'amount_due' => $amountDue,

            'balance' => max(
                0,
                $amountDue - $amount
            ),

            'currency' => $this->currencyValue(
                $perception->devise
            ),

            'date' => $perception->paid_at?->toISOString(),

            'academic_year_id' => $perception->annee_id,
        ];
    }

    /**
     * Normalize the Devise enum/value for the API.
     */
    private function currencyValue(
        mixed $currency
    ): ?string {
        if ($currency instanceof Devise) {
            return $currency->value;
        }

        return $currency !== null
            ? (string) $currency
            : null;
    }

    /**
     * Standard pagination structure for Flutter.
     */
    private function pagination(
        LengthAwarePaginator $paginator
    ): array {
        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
            'has_more_pages' => $paginator->hasMorePages(),
        ];
    }
}
