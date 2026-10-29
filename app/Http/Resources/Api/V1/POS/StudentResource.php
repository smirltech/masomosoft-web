<?php

namespace App\Http\Resources\Api\V1\POS;

use App\Http\Resources\Api\BaseResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public static function collection($resource): BaseResourceCollection
    {
        return new BaseResourceCollection(
            $resource,
            static::class
        );
    }

    public function toArray($request): array|\JsonSerializable|\Illuminate\Contracts\Support\Arrayable
    {

        return [
            'id' => $this->id,
            'name' => trim($this->nom),
            'matricule' => $this->matricule,
            'class' => $this->inscription?->classe?->code,
        ];

    }
}
