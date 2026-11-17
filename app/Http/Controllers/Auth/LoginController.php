<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    function login(LoginRequest $request) {
        $credentials = $request->validated();

        if( ! $credentials) {
            return response()->json([
                "message" => "Informations invalides",
                "data" => []
            ], 422);
        }

        $user = Auth::attempt($credentials, $remenber = true);
        if($user) {
            return response() ->json([
                "message" => "Authentification réussie",
                "data" => [
                    "user" => $user
                ]
            ]);
        }
        return response()->json([
            "message" => "Erreur lors de l'authentification",
            "data" => []
        ], 422);
    }
}
