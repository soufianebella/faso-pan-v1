<?php

namespace Database\Factories;

use App\Models\Campagne;
use Illuminate\Database\Eloquent\Factories\Factory;

class CampagneFactory extends Factory
{
    protected $model = Campagne::class;

    public function definition(): array
    {
        return [
            'nom' => 'Campagne ' . fake()->word(),
            'annonceur' => fake()->company(),
            'date_debut' => now(),
            'date_fin' => now()->addMonth(),
            'statut' => 'active',
            'created_by' => \App\Models\User::factory(),
        ];
    }
}
