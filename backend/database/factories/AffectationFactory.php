<?php

namespace Database\Factories;

use App\Models\Affectation;
use App\Models\Campagne;
use App\Models\Face;
use Illuminate\Database\Eloquent\Factories\Factory;

class AffectationFactory extends Factory
{
    protected $model = Affectation::class;

    public function definition(): array
    {
        return [
            'campagne_id' => Campagne::factory(),
            'face_id' => Face::factory(),
            'date_debut' => now(),
            'date_fin' => now()->addMonth(),
        ];
    }
}
