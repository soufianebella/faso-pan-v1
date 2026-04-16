<?php

namespace Database\Factories;

use App\Models\Face;
use App\Models\Panneau;
use Illuminate\Database\Eloquent\Factories\Factory;

class FaceFactory extends Factory
{
    protected $model = Face::class;

    public function definition(): array
    {
        return [
            'panneau_id' => Panneau::factory(),
            'numero' => fake()->numberBetween(1, 2),
            'largeur' => 4.00,
            'hauteur' => 3.00,
            'statut' => 'libre',
        ];
    }
}
