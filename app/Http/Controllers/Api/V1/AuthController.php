<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants sont incorrects.'],
            ]);
        }

        $deviceName = $credentials['device_name']
            ?? 'MasomoSoft Manager';

        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,

                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $this->getUserRole($user),
                    'device_role' => $this->getDeviceRole($user),
                ],
            ],
        ]);
    }


    public function logout(Request $request): JsonResponse
    {
        $request->user()
            ->currentAccessToken()
            ?->delete();

        return response()->json([
            'message' => 'Successfully logged out.',
        ]);
    }



    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $this->getUserRole($user),
            ],
        ]);
    }


    private function getUserRole(User $user): ?string
    {
        if (method_exists($user, 'getRoleNames')) {
            return $user->getRoleNames()->first();
        }

        return $user->role ?? null;
    }


    private function getDeviceRole(User $user): string
    {
        // Check the explicit financial role.
        if (
            method_exists($user, 'hasRole')
            && $user->hasRole('financier')
        ) {
            return 'pos';
        }


        if (method_exists($user, 'getAllPermissions')) {
            $financePrefixes = [
                'depenses.',
                'revenus.',
                'frais.',
                'perceptions.',
            ];

            $permissions = $user->getAllPermissions()
                ->pluck('name');

            foreach ($permissions as $permission) {
                foreach ($financePrefixes as $prefix) {
                    if (str_starts_with($permission, $prefix)) {
                        return 'pos';
                    }
                }
            }
        }

        return 'user';
    }
}
