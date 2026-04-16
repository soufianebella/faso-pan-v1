<?php

namespace Database\Factories;

use App\Models\Affectation;
use App\Models\Tache;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TacheFactory extends Factory
{
    protected $model = Tache::class;

    public function definition(): array
    {
        return [
            'affectation_id' => Affectation::factory(),
            'agent_id' => User::factory(),
            'statut' => 'en_attente',
            'note' => fake()->sentence(),
        ];
    }
}
