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
    private const DEVICE_ROLE_POS  = 'pos';
    private const DEVICE_ROLE_USER = 'user';

    private const POS_ROLES = ['financier'];

    private const FINANCE_PERMISSION_PREFIXES = [
        'depenses.',
        'revenus.',
        'frais.',
        'perceptions.',
    ];

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'       => ['required', 'string'],
            'password'    => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants sont incorrects.'],
            ]);
        }


        $tokenName = $credentials['device_name'] ?? 'MasomoSoft Manager';

        $token = $user->createToken($tokenName)->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'user'  => $this->formatUser($user),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Successfully logged out.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->formatUser($request->user()),
        ]);
    }

    private function formatUser(User $user): array
    {
        return [
            'id'          => $user->id,
            'name'        => $user->name,
            'email'       => $user->email,
            'role'        => $this->getUserRole($user),
            'device_role' => $this->getDeviceRole($user),
        ];
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
        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(self::POS_ROLES)) {
            return self::DEVICE_ROLE_POS;
        }

        if (method_exists($user, 'getAllPermissions')) {
            foreach ($user->getAllPermissions()->pluck('name') as $permission) {
                foreach (self::FINANCE_PERMISSION_PREFIXES as $prefix) {
                    if (str_starts_with($permission, $prefix)) {
                        return self::DEVICE_ROLE_POS;
                    }
                }
            }
        }

        return self::DEVICE_ROLE_USER;
    }
}
