<?php

namespace App\Http\Controllers\Api\V1\Pos;

use App\Enums\Devise;
use App\Enums\FraisType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePaymentRequest;
use App\Http\Resources\PerceptionResource;
use App\Models\Annee;
use App\Models\Eleve;
use App\Models\Frais;
use App\Models\Perception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class PosPaymentController extends Controller
{
    /**
     * Create POS payment.
     *
     * The payment reference is stored in perceptions.reference
     * and can later be retrieved using:
     *
     * POST /api/v1/pos/payments
     */
    public function store(StorePaymentRequest $request)
    {
        $user = $request->user();
        $annee_id = Annee::id();

        if (!$annee_id) {
            return response()->json([
                'message' => 'No current academic year found.',
            ], 422);
        }

        $validated = $request->validated();
        $paymentCurrency = $validated['currency'];

        $studentIdentifier = $validated['student_id'];
        $student = Eleve::find($studentIdentifier);
        $inscription = $student->inscriptions()->where('annee_id', $annee_id)->first();

        if (!$inscription) {
            throw ValidationException::withMessages([
                'student_id' => [
                    'Student is not registered for the current academic year.',
                ],
            ]);
        }

        $totalPaid = 0;


        $fee = Frais::find($validated['fee_id']);

        $amountDue = $fee->montant;


        $requestedAmount = $validated['amount'];


        $perception = new Perception();

        $perception->user_id = $user->id;
        $perception->frais_id = $fee->id;
        $perception->inscription_id = $inscription->id;
        $perception->annee_id = $annee_id;
        $perception->custom_property = $validated['period'] ?? null;
        $perception->montant = $requestedAmount;
        $perception->devise = $paymentCurrency;
        $perception->frais_montant = $amountDue;
        $perception->paid_at = $validated['paid_at'];


        $perception->save();


        return PerceptionResource::make($perception);
    }


    public function show(Perception $perception)
    {
        return PerceptionResource::make($perception);
    }


}
