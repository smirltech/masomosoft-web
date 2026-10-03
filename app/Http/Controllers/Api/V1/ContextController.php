<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Annee;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContextController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $school = School::first();
        $user = $request->user();

        return response()->json([
            'data' => [
                'school' => $school ? [
                    'id' => $school->id,
                    'name' => $school->name,
                    'code' => $school->code,
                    'logo' => $school->logo,
                    'address' => $school->address,
                    'phone' => $school->phone,
                    'email' => $school->email,
                    'website' => $school->website,
                    'settings' => $school->settings,
                ] : null,

                'academic_year' => $this->getCurrentAcademicYear(),

                'supported_currencies' => [
                    'USD',
                    'CDF',
                ],

                'device' => $this->getDevice($request),

                'permissions' => $this->getPermissions($user),
            ],
        ]);
    }
    private function getCurrentAcademicYear(): ?array
    {

        return Annee::encours()->toArray();
    }
    private function getDevice(Request $request): array
    {

        return [
            'device_id' => $request->header('X-Device-Id', $request->query('device_id')),
            'device_name' => $request->header('X-Device-Name', $request->query('device_name')),
            'registered' => true,
        ];
    }

    private function getPermissions($user): array
    {
        if (! $user) {
            return [];
        }

        return $user->getAllPermissions()
            ->pluck('name')
            ->values()
            ->all();
    }
}
