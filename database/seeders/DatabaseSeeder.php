<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Jeu de démonstration : un compte de test, une collection
     * et trois objets (images inline SVG encodées en data URI,
     * exactement comme le fera l'upload base64 du front).
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@collection.dev'],
            [
                'lastname' => 'Démo',
                'firstname' => 'Mehdi',
                'password' => Hash::make('password1234'),
            ]
        );

        $collection = $user->collections()->firstOrCreate(
            ['title' => 'Collection mix']
        );

        if ($collection->items()->exists()) {
            $this->command?->info('Données de démo déjà présentes.');

            return;
        }

        $collection->items()->createMany([
            [
                'name' => 'Pièce de 1972',
                'description' => 'Pièce de 50 centimes de francs.',
                'image' => $this->svgDataUri('coin'),
                'rarity' => 'Common',
                'price' => 170,
            ],
            [
                'name' => 'Linx',
                'description' => 'Une épée légendaire d’une netteté et d’une histoire inégalées.',
                'image' => $this->svgDataUri('figurine'),
                'rarity' => 'Legendary',
                'price' => 250,
            ],
            [
                'name' => 'Timbre 1800',
                'description' => 'Un vieux timbre.',
                'image' => $this->svgDataUri('stamp'),
                'rarity' => 'Rare',
                'price' => 555,
            ],
        ]);

        $this->command?->info('Compte démo : demo@collection.dev / password1234');
    }

    /**
     * Construire une petite illustration SVG encodée en data URI,
     * par type d'objet.
     */
    private function svgDataUri(string $kind): string
    {
        $svg = match ($kind) {
            'coin' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120">'
                .'<circle cx="60" cy="60" r="56" fill="#e6ddc0" stroke="#9a7a2c" stroke-width="3"/>'
                .'<circle cx="60" cy="60" r="41" fill="#dcbc61" stroke="#8a6c25" stroke-width="2"/>'
                .'<text x="60" y="66" text-anchor="middle" font-family="Georgia, serif" font-size="24" font-weight="bold" fill="#6f5719">1972</text>'
                .'</svg>',
            'figurine' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 150">'
                .'<ellipse cx="60" cy="136" rx="29" ry="8" fill="#5b4330"/>'
                .'<rect x="48" y="104" width="9" height="26" rx="4" fill="#3c5d86"/>'
                .'<rect x="63" y="104" width="9" height="26" rx="4" fill="#3c5d86"/>'
                .'<path d="M44 70 q16 -8 32 0 l4 30 q-20 7 -40 0 z" fill="#2f6fa8"/>'
                .'<circle cx="60" cy="55" r="12.5" fill="#f2c49b"/>'
                .'<path d="M47 54 q0 -14 13 -14 q13 0 13 14 q-4 -6 -13 -6 q-9 0 -13 6 z" fill="#7a4a24"/>'
                .'</svg>',
            'stamp' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 150">'
                .'<rect x="15" y="15" width="90" height="120" rx="6" fill="#e8e0cd" stroke="#a89a78" stroke-width="3"/>'
                .'<rect x="28" y="28" width="64" height="80" fill="#c56a4b"/>'
                .'<circle cx="60" cy="60" r="18" fill="#f0e3cf"/>'
                .'<text x="60" y="66" text-anchor="middle" font-family="Georgia, serif" font-size="14" font-weight="bold" fill="#8a5a3b">1800</text>'
                .'</svg>',
            default => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120">'
                .'<rect x="6" y="6" width="108" height="108" rx="12" fill="#eef2f9" stroke="#b9c4d6" stroke-width="2" stroke-dasharray="6 6"/>'
                .'</svg>',
        };

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
