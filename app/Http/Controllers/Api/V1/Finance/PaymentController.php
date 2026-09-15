<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Enums\Devise;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Finance\PaymentStoreRequest;
use App\Models\Annee;
use App\Models\Perception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function store(PaymentStoreRequest $request): JsonResponse
    {
        $perception = DB::transaction(function () use ($request) {
            $perception = $request->filled('perception_id')
                ? Perception::query()->lockForUpdate()->findOrFail($request->string('perception_id')->toString())
                : Perception::query()
                    ->whereHas('inscription', fn ($query) => $query->where('eleve_id', $request->string('student_id')->toString()))
                    ->where('devise', $request->string('currency')->toString())
                    ->where('frais_montant', (float) $request->input('amount'))
                    ->whereNull('paid_at')
                    ->when(Annee::encours(), fn ($query, $annee) => $query->where('annee_id', $annee->id))
                    ->lockForUpdate()->firstOrFail();

            if ($perception->paid_at) {
                abort(409, 'Cette perception a déjà été payée.');
            }

            $perception->forceFill([
                'montant' => $request->filled('amount') ? (float) $request->input('amount') : $perception->montant,
                'paid_by' => $request->input('paid_by', auth()->id()),
                'paid_at' => now(),
            ])->save();

            return $perception->fresh();
        });

        return response()->json([
            'data' => [
                'transactionId' => $perception->id,
                'status' => 'success',
                'currency' => $perception->devise instanceof Devise ? $perception->devise->value : $perception->devise,
                'amount' => (float) $perception->montant,
            ],
        ], 201);
    }
}
