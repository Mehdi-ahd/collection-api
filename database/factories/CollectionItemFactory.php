<?php

namespace Database\Factories;

use App\Models\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CollectionItem>
 */
class CollectionItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'collection_id' => Collection::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'image' => null,
            'rarity' => fake()->randomElement(['Common', 'Uncommon', 'Rare', 'Legendary']),
            'price' => fake()->numberBetween(0, 1000),
        ];
    }
}
