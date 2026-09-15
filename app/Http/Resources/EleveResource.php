<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EleveResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'matricule' => $this->matricule,
            'nom' => $this->nom,
            'sexe' => $this->sexe?->value ?? $this->sexe,
            'date_naissance' => $this->date_naissance,
            'adresse' => $this->adresse,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'inscriptions' => InscriptionResource::collection($this->whenLoaded('inscriptions')),
        ];
    }
}
