<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    function register(RegisterRequest $request) {
        $data = $request->validated();

        if(!$request) {
            return response()->json([
                "message" => "Données invalides",
                "data" => []
            ], 422);
        }

        $user = User::create($data);
        if($user) {
            return response()->json([
                "message" => "Utilisateur crée avec succes",
                "data" => [
                    "user" => $user
                ]
            ]);
        }

        return response()->json([
            "message" => "Erreur lors de la création du compte",
            "data" => []
        ]);
    }
}
