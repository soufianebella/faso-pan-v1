<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Notification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /** @test */
    public function user_can_list_their_unread_notifications()
    {
        /** @var User $user */
        $user = User::factory()->create();
        Notification::factory()->count(3)->create([
            'user_id' => $user->id,
            'lu_at' => null
        ]);
        Notification::factory()->count(2)->create([
            'user_id' => $user->id,
            'lu_at' => now()
        ]);

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/v1/notifications');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data')
                 ->assertJsonPath('total', 3);
    }

    /** @test */
    public function user_can_get_unread_notifications_count()
    {
        /** @var User $user */
        $user = User::factory()->create();
        Notification::factory()->count(5)->create([
            'user_id' => $user->id,
            'lu_at' => null
        ]);

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/v1/notifications/count');

        $response->assertStatus(200)
                 ->assertJsonPath('count', 5);
    }

    /** @test */
    public function user_can_mark_notification_as_read()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'lu_at' => null
        ]);

        $response = $this->actingAs($user, 'sanctum')
                         ->patchJson("/api/v1/notifications/{$notification->id}/lue");

        $response->assertStatus(200);
        $this->assertNotNull($notification->fresh()->lu_at);
    }

    /** @test */
    public function user_cannot_mark_others_notification_as_read()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $notification = Notification::factory()->create([
            'user_id' => $otherUser->id,
            'lu_at' => null
        ]);

        $response = $this->actingAs($user, 'sanctum')
                         ->patchJson("/api/v1/notifications/{$notification->id}/lue");

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_mark_all_notifications_as_read()
    {
        /** @var User $user */
        $user = User::factory()->create();
        Notification::factory()->count(3)->create([
            'user_id' => $user->id,
            'lu_at' => null
        ]);

        $response = $this->actingAs($user, 'sanctum')
                         ->patchJson('/api/v1/notifications/toutes-lues');

        $response->assertStatus(200);
        $this->assertEquals(0, Notification::where('user_id', $user->id)->whereNull('lu_at')->count());
    }
}
