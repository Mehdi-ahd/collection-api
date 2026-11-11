<?php

namespace App\Http\Controllers;

use App\Http\Requests\Collection\StoreCollectionRequest;
use App\Http\Requests\Collection\UpdateCollectionRequest;
use App\Models\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    /**
     * Lister les collections de l'utilisateur connecté (avec leurs objets).
     */
    public function index(Request $request): JsonResponse
    {
        $collections = $request->user()
            ->collections()
            ->with('items')
            ->orderBy('id')
            ->get();

        return response()->json([
            'message' => 'Collections récupérées avec succès',
            'data' => $collections,
        ]);
    }

    /**
     * Créer une nouvelle collection pour l'utilisateur connecté.
     */
    public function store(StoreCollectionRequest $request): JsonResponse
    {
        $collection = $request->user()->collections()->create($request->validated());

        return response()->json([
            'message' => 'Collection créée avec succès',
            'data' => $collection->load('items'),
        ], 201);
    }

    /**
     * Afficher une collection de l'utilisateur connecté (404 sinon).
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $collection = $this->findOwnedCollection($request, $id);

        if (! $collection) {
            return $this->notFound('Collection introuvable');
        }

        return response()->json([
            'message' => 'Collection récupérée avec succès',
            'data' => $collection,
        ]);
    }

    /**
     * Mettre à jour une collection de l'utilisateur connecté.
     */
    public function update(UpdateCollectionRequest $request, int $id): JsonResponse
    {
        $collection = $this->findOwnedCollection($request, $id);

        if (! $collection) {
            return $this->notFound('Collection introuvable');
        }

        $collection->update($request->validated());

        return response()->json([
            'message' => 'Collection mise à jour avec succès',
            'data' => $collection->load('items'),
        ]);
    }

    /**
     * Supprimer une collection de l'utilisateur connecté (objets inclus).
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $collection = $this->findOwnedCollection($request, $id);

        if (! $collection) {
            return $this->notFound('Collection introuvable');
        }

        $collection->delete();

        return response()->json([
            'message' => 'Collection supprimée avec succès',
            'data' => [],
        ]);
    }

    /**
     * Retrouver une collection appartenant à l'utilisateur : toute
     * collection inexistante ou d'un autre utilisateur renvoie null,
     * ce qui évite de révéler l'existence des ressources d'autrui.
     */
    private function findOwnedCollection(Request $request, int $id): ?Collection
    {
        return $request->user()
            ->collections()
            ->with('items')
            ->find($id);
    }

    /**
     * Réponse 404 uniforme pour une ressource introuvable.
     */
    private function notFound(string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => [],
        ], 404);
    }
}
