<?php

namespace App\Http\Controllers;

use App\Http\Requests\CollectionItem\StoreCollectionItemRequest;
use App\Http\Requests\CollectionItem\UpdateCollectionItemRequest;
use App\Models\Collection;
use App\Models\CollectionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CollectionItemController extends Controller
{
    /**
     * Créer un objet de collection dans une collection de l'utilisateur.
     */
    public function store(StoreCollectionItemRequest $request, Collection $collection): JsonResponse
    {
        if (! $this->owns($request, $collection)) {
            return $this->notFound('Collection introuvable');
        }

        $item = $collection->items()->create($request->validated());

        return response()->json([
            'message' => 'Objet créé avec succès',
            'data' => $item,
        ], 201);
    }

    /**
     * Afficher un objet de collection (pour préremplir l'édition).
     */
    public function show(Request $request, CollectionItem $collectionItem): JsonResponse
    {
        if (! $this->ownsItem($request, $collectionItem)) {
            return $this->notFound('Objet introuvable');
        }

        return response()->json([
            'message' => 'Objet récupéré avec succès',
            'data' => $collectionItem,
        ]);
    }

    /**
     * Mettre à jour un objet de collection.
     */
    public function update(UpdateCollectionItemRequest $request, CollectionItem $collectionItem): JsonResponse
    {
        if (! $this->ownsItem($request, $collectionItem)) {
            return $this->notFound('Objet introuvable');
        }

        $collectionItem->update($request->validated());

        return response()->json([
            'message' => 'Objet mis à jour avec succès',
            'data' => $collectionItem,
        ]);
    }

    /**
     * Supprimer un objet de collection.
     */
    public function destroy(Request $request, CollectionItem $collectionItem): JsonResponse
    {
        if (! $this->ownsItem($request, $collectionItem)) {
            return $this->notFound('Objet introuvable');
        }

        $collectionItem->delete();

        return response()->json([
            'message' => 'Objet supprimé avec succès',
            'data' => [],
        ]);
    }

    /**
     * Vérifier que la collection appartient à l'utilisateur connecté.
     */
    private function owns(Request $request, Collection $collection): bool
    {
        return $collection->user_id === $request->user()->id;
    }

    /**
     * Vérifier que l'objet appartient à une collection de l'utilisateur.
     */
    private function ownsItem(Request $request, CollectionItem $item): bool
    {
        return $item->collection()->value('user_id') === $request->user()->id;
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
