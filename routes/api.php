<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\CollectionItemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::name('auth')->group(function () {
    Route::post('/login', [LoginController::class, 'login'])->name('login');
    Route::post('/register', [RegisterController::class, 'register'])->name('register');
});

Route::middleware('auth:sanctum')->group(function () {
    // Utilisateur connecté : utilisé au rechargement de la page (F5)
    // pour restaurer currentUser à partir du token persistant.
    Route::get('/user', function (Request $request) {
        return response()->json([
            'message' => 'Utilisateur récupéré avec succès',
            'data' => [
                'user' => $request->user(),
            ],
        ]);
    })->name('auth.user');

    // Collections de l'utilisateur (index/show/store/update/destroy).
    Route::apiResource('collection', CollectionController::class)
        ->parameters(['collection' => 'collection']);

    // Objets de collection : création rattachée à une collection,
    // lecture/mise à jour/suppression par l'id de l'objet.
    Route::post('collection/{collection}/item', [CollectionItemController::class, 'store']);
    Route::get('item/{collectionItem}', [CollectionItemController::class, 'show']);
    Route::match(['put', 'patch'], 'item/{collectionItem}', [CollectionItemController::class, 'update']);
    Route::delete('item/{collectionItem}', [CollectionItemController::class, 'destroy']);

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
