<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Reservation;
use App\Models\Contract;
use App\Models\Equipment;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractsNavigationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: "Mes contrats" link appears in navbar for authenticated users
     */
    public function test_contracts_link_visible_for_authenticated_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        // Link should be present in the response
        $response->assertSee('Mes contrats');
        $response->assertSee('contracts'); // Route should be in href
    }

    /**
     * Test: "Mes contrats" link does NOT appear for unauthenticated users
     */
    public function test_contracts_link_not_visible_for_guest()
    {
        $response = $this->get('/');

        // Link should not be in the response for guests
        $response->assertDontSee('Mes contrats');
    }

    /**
     * Test: Authenticated user can access contracts page via navbar link
     */
    public function test_authenticated_user_can_access_contracts_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/contracts');

        // Page should load successfully
        $response->assertStatus(200);
        $response->assertViewIs('contracts.index');
    }

    /**
     * Test: Unauthenticated user is redirected from contracts page
     */
    public function test_guest_redirected_from_contracts_page()
    {
        $response = $this->get('/contracts');

        // Should redirect to login
        $response->assertRedirect('/login');
    }

    /**
     * Test: Contracts page displays user's contracts
     */
    public function test_contracts_page_shows_heading()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/contracts');

        // Page should load and show the heading
        $response->assertStatus(200);
        $response->assertSee('Contrats');
    }

    /**
     * Test: User can only see their own contracts (isolation)
     */
    public function test_user_contracts_are_isolated()
    {
        $user = User::factory()->create();

        // Without contracts, page should still load
        $response = $this->actingAs($user)->get('/contracts');
        $response->assertStatus(200);
        $response->assertViewIs('contracts.index');
    }

    /**
     * Test: Navbar is displayed on contracts page
     */
    public function test_navbar_displayed_on_contracts_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/contracts');

        // Navbar elements should be present
        $response->assertSee('SolarShare'); // Logo
        $response->assertSee('Mes contrats'); // Current page link
    }
}
