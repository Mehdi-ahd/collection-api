<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Créer un utilisateur et obtenir son token Sanctum.
     *
     * @return array{0: User, 1: string}
     */
    private function loginUser(): array
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        return [$user, $response->json('data.token')];
    }

    public function test_refuse_les_collections_sans_etre_authentifie(): void
    {
        $response = $this->getJson('/api/collection');

        $response->assertStatus(401)
            ->assertJsonStructure(['message']);
    }

    public function test_liste_uniquement_les_collections_de_l_utilisateur_connecte(): void
    {
        [$user, $token] = $this->loginUser();
        $other = User::factory()->create();

        $mine = Collection::factory()->for($user)->create();
        Collection::factory()->for($other)->count(3)->create();

        $response = $this->getJson('/api/collection', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'data'])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', $mine->title);
    }

    public function test_cree_une_collection_pour_l_utilisateur_connecte(): void
    {
        [$user, $token] = $this->loginUser();

        $response = $this->postJson('/api/collection', [
            'title' => 'Timbres rares',
        ], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Timbres rares')
            ->assertJsonPath('data.user_id', $user->id);

        $this->assertTrue(
            $user->collections()->where('title', 'Timbres rares')->exists()
        );
    }

    public function test_refuse_un_titre_de_collection_invalide(): void
    {
        [$user, $token] = $this->loginUser();

        $this->postJson('/api/collection', [
            'title' => '',
        ], [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['title']]);
    }

    public function test_affiche_une_collection_avec_ses_objets_et_refuse_celles_d_autrui(): void
    {
        [$user, $token] = $this->loginUser();
        $collection = Collection::factory()->for($user)
            ->has(CollectionItem::factory()->count(2), 'items')->create();

        $this->getJson("/api/collection/{$collection->id}", [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(200)
            ->assertJsonCount(2, 'data.items');

        $otherCollection = Collection::factory()->create();

        $this->getJson("/api/collection/{$otherCollection->id}", [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(404);
    }

    public function test_cree_un_objet_dans_une_collection_de_l_utilisateur(): void
    {
        [$user, $token] = $this->loginUser();
        $collection = Collection::factory()->for($user)->create();

        $payload = [
            'name' => 'Pièce de 1972',
            'description' => 'Une pièce.',
            'rarity' => 'Common',
            'price' => 170,
        ];

        $response = $this->postJson("/api/collection/{$collection->id}/item", $payload, [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Pièce de 1972');

        $this->assertSame(1, $collection->items()->count());
    }

    public function test_refuse_une_rarite_inconnue_et_un_prix_negatif(): void
    {
        [$user, $token] = $this->loginUser();
        $collection = Collection::factory()->for($user)->create();

        $this->postJson("/api/collection/{$collection->id}/item", [
            'name' => 'X',
            'description' => 'Y',
            'rarity' => 'Imposssible',
            'price' => -5,
        ], [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['rarity', 'price']]);
    }

    public function test_met_a_jour_puis_supprime_un_objet_de_collection(): void
    {
        [$user, $token] = $this->loginUser();
        $collection = Collection::factory()->for($user)->create();
        $item = CollectionItem::factory()->for($collection)->create();

        $this->putJson("/api/item/{$item->id}", [
            'name' => 'Linx modifié',
            'description' => $item->description,
            'rarity' => $item->rarity,
            'price' => 999,
        ], [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(200)
            ->assertJsonPath('data.name', 'Linx modifié')
            ->assertJsonPath('data.price', 999);

        $this->deleteJson("/api/item/{$item->id}", [], [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(200);

        $this->assertSame(0, $collection->items()->count());
    }

    public function test_refuse_de_modifier_un_objet_appartenant_a_un_autre_utilisateur(): void
    {
        [$user, $token] = $this->loginUser();
        $otherCollection = Collection::factory()->create();
        $otherItem = CollectionItem::factory()->for($otherCollection)->create();

        $this->putJson("/api/item/{$otherItem->id}", [
            'name' => 'Piraté',
            'description' => 'y',
            'rarity' => 'Common',
            'price' => 1,
        ], [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(404);
    }
}
