<?php

namespace App\Http\Controllers\Api;

use App\Models\Classe;
use Orion\Concerns\DisableAuthorization;
use Orion\Http\Controllers\Controller;

class ClasseController extends Controller
{
    use DisableAuthorization;

    protected $model = Classe::class;

    public function includes(): array
    {
        return ['inscriptions'];
    }

}
