<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\ResourceCollection;

class BaseResourceCollection extends ResourceCollection
{
    public $collects;

    public function __construct($resource, string $collects)
    {
        $this->collects = $collects;

        parent::__construct($resource);
    }

    public function paginationInformation(
        $request,
        $paginated,
        $default
    ): array {
        /**
         * @param  \Illuminate\Http\Request  $request
         * @param  array  $paginated
         * @param  array  $default
         * @return array
         */
        return [
            'pagination' => [
                'current_page' => $paginated['current_page'],
                'per_page'     => $paginated['per_page'],
                'total'        => $paginated['total'],
                'last_page'    => $paginated['last_page'],
                'next_page'    => $this->resource->nextPageUrl(),
                'prev_page'    => $this->resource->previousPageUrl(),
            ],
        ];
    }
}
