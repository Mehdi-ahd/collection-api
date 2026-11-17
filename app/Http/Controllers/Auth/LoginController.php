<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Authentifier un utilisateur et générer son token API.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Identifiants invalides',
                'data' => [],
            ], 401);
        }

        /** @var User $user */
        $user = Auth::user();

        return response()->json([
            'message' => 'Authentification réussie',
            'data' => [
                'user' => $user,
                'token' => $user->createToken('spa')->plainTextToken,
            ],
        ]);
    }

    /**
     * Révoquer le token utilisé pour la requête en cours.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie',
            'data' => [],
        ]);
    }
}
