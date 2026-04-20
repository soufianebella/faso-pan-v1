<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type'    => $this->faker->randomElement(['nouvelle_tache', 'campagne_creee', 'expiration_j7']),
            'titre'   => $this->faker->sentence(3),
            'message' => $this->faker->sentence(10),
            'lien'    => '/taches',
            'lu_at'   => null,
        ];
    }
}
