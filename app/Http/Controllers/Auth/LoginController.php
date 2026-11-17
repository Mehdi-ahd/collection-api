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
    function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated(); // données valides garantie

        if (! Auth::attempt($credentials)) {   // ← seul vrai test d'échec ici : l'authentification
            return response()->json([
                "message" => "Identifiants invalides",
                "data" => [],
            ], 401);
        }

        $user = Auth::user();

        /** @var User $user */
        return response()->json([
            "message" => "Authentification réussie",
            "data" => [
                "user" => $user,
                "token" => $user->createToken("spa")->plainTextToken,
            ],
        ]);
    }

    function logout(Request $request) {
        Auth::logout();
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            "message" => "Déconnexion réussie",
            "data" => []
        ]);
    }
}
