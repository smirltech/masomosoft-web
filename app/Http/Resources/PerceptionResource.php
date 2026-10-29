<?php

namespace App\Http\Resources;

use App\Enums\Devise;
use App\Enums\MinervalMonth;
use Illuminate\Http\Resources\Json\JsonResource;

class PerceptionResource extends JsonResource
{
    public function toArray($request)
    {

        $inscription = $this->inscription;
        $student = $inscription?->eleve;
        $fee = $this->frais;
        $studentName = $student->nom;
        $classCode = $inscription?->classe_code?->code;

        return [
            'data' => [
                'id' => $this->id,
                'reference' => $this->reference,
                'student' => [
                    'id' => $student?->id,
                    'name' => $studentName,
                    'matricule' => $student?->matricule,
                    'class' => $classCode,
                ],
                'fee' =>
                    [
                        'id' => $fee->id,
                        'name' => $fee->nom,
                        'amount' => $this->frais_montant,
                        'period'=> MinervalMonth::tryFrom($this->custom_property)->label(),
                    ],
                'paid_amount' => $this->montant,
                'outstanding_amount'=>null,
                'currency' => $this->devise,
                'paid_at' => $this->paid_at,
                'created_at'=>$this->created_at,
                'teller' => $this->userDisplayName($this->user),
            ],
        ];
    }





    private
    function userDisplayName($user): string
    {

        return $user->name;

    }
}
