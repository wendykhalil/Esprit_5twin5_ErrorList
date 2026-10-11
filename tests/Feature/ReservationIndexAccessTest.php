<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationIndexAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_reservations_index()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $response = $this->actingAs($admin)->get(route('admin.reservations.index'));
        
        $response->assertStatus(200);
        $response->assertViewIs('backend.reservations.index');
    }

    public function test_client_cannot_access_reservations_index()
    {
        $client = User::factory()->create(['role' => 'client']);
        
        $response = $this->actingAs($client)->get(route('admin.reservations.index'));
        
        $response->assertStatus(403);
    }

    public function test_unauthenticated_cannot_access_reservations_index()
    {
        $response = $this->get(route('admin.reservations.index'));
        
        $response->assertRedirect(route('login'));
    }
}
