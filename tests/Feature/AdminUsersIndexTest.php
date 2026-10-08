<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUsersIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_admin_users_index_lists_database_users(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create([
            'name' => 'Utilisateur Test Liste',
            'email' => 'liste.test@example.com',
            'role' => 'client',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users'));

        $response
            ->assertOk()
            ->assertSee($client->name)
            ->assertSee($client->email)
            ->assertSee('Client');
    }
}
