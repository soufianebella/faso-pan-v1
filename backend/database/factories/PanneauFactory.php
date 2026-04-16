<?php

namespace Database\Factories;

use App\Models\Panneau;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PanneauFactory extends Factory
{
    protected $model = Panneau::class;

    public function definition(): array
    {
        return [
            'reference' => 'PAN-' . fake()->unique()->numerify('####'),
            'ville' => fake()->city(),
            'quartier' => fake()->streetName(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'statut' => 'actif',
            'created_by' => User::factory(),
        ];
    }
}
