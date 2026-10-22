<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Reservation;
use App\Models\Equipment;
use App\Models\Category;
use App\Models\Reclamation;
use App\Models\CompensationCode;
use App\Models\Incident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

/**
 * Test Suite: Client Reclamation Reward UX with Animated Compensation Code Card
 *
 * Tests:
 * - Client viewing their own approved reclamations with animated reward card
 * - Compensation code display and copy functionality
 * - Security: clients cannot view other users' reclamations/codes
 * - Unauthenticated users cannot access reclamation pages
 * - Proper authorization checks on CompensationCodeController
 */
class ClientReclamationRewardUXTest extends TestCase
{
    use RefreshDatabase;

    private User $clientA;
    private User $clientB;
    private User $equipmentOwner;
    private Equipment $equipment;
    private Reservation $reservation;
    private Reclamation $approvedReclamation;
    private CompensationCode $compensationCode;

    protected function setUp(): void
    {
        parent::setUp();

        // Create users
        $this->clientA = User::factory()->create(['role' => 'client', 'email' => 'client-a@test.com']);
        $this->clientB = User::factory()->create(['role' => 'client', 'email' => 'client-b@test.com']);
        $this->equipmentOwner = User::factory()->create(['role' => 'client']);

        // Create category and equipment
        $category = Category::firstOrCreate(['name' => 'Solar Panels']);
        $this->equipment = Equipment::create([
            'user_id' => $this->equipmentOwner->id,
            'category_id' => $category->id,
            'name' => 'Premium Solar Panel',
            'description' => 'High efficiency solar panel',
            'price_per_day' => 150,
            'condition' => 'excellent',
            'power' => 500,
            'capacity' => 5000,
            'location' => 'Paris, France',
            'availability' => true,
            'status' => 'active',
        ]);

        // Create completed reservation for clientA
        $this->reservation = Reservation::create([
            'user_id' => $this->clientA->id,
            'equipment_id' => $this->equipment->id,
            'date_debut' => now()->subDays(30),
            'date_fin' => now()->subDays(5),
            'statut' => 'terminee',
            'prix_total' => 750,
        ]);

        // Create an approved reclamation for clientA
        $this->approvedReclamation = Reclamation::create([
            'user_id' => $this->clientA->id,
            'reservation_id' => $this->reservation->id,
            'title' => 'Equipment Damage During Rental',
            'description' => 'Panel was slightly damaged during rental period',
            'status' => 'approuvee',
            'refund_amount' => 50,
            'refund_approved_at' => now(),
        ]);

        // Create an incident
        Incident::create([
            'reclamation_id' => $this->approvedReclamation->id,
            'equipment_id' => $this->equipment->id,
            'type_probleme' => 'endommage', 'titre' => 'Incident signale',
            'description' => 'Minor scratch on panel surface',
            'damage_percentage' => 5,
            'proposed_refund_percentage' => 30,
            'proposed_refund_amount' => 45,
        ]);

        // Create compensation code assigned to the reclamation
        $this->compensationCode = CompensationCode::create([
            'code' => 'SOLAR-REWARD-' . strtoupper(uniqid()),
            'type' => 'percentage',
            'value' => 15,
            'user_id' => $this->clientA->id,
            'reclamation_id' => $this->approvedReclamation->id,
            'valid_from' => now(),
            'valid_until' => now()->addDays(30),
            'max_uses' => 1,
            'uses_count' => 0,
            'is_active' => true,
            'description' => 'Compensation for approved reclamation #' . $this->approvedReclamation->id,
        ]);
    }

    // ===================================================================
    // FEATURE: Animated Reward Card Display
    // ===================================================================

    /**
     * Test: Approved reclamation shows animated reward card with compensation code
     */
    public function test_approved_reclamation_shows_animated_reward_card_with_code(): void
    {
        $this->actingAs($this->clientA);

        $response = $this->get(route('reclamations.show', $this->approvedReclamation->id));

        $response->assertStatus(200);
        // Check for reward card elements
        $response->assertSee('Réclamation Approuvée !', false);
        $response->assertSee('Vous avez reçu un code de compensation', false);
        $response->assertSee($this->compensationCode->code);
        $response->assertSee('de réduction sur votre prochaine location');
        $response->assertSee('Copier le code');
        $response->assertSee('Voir les détails');
    }

    /**
     * Test: Pending reclamation does NOT show reward card
     */
    public function test_pending_reclamation_does_not_show_reward_card(): void
    {
        // Create a pending reclamation without compensation code
        $pendingReclamation = Reclamation::create([
            'user_id' => $this->clientA->id,
            'reservation_id' => $this->reservation->id,
            'title' => 'Another Damage Claim',
            'description' => 'Different damage incident',
            'status' => 'en_attente',
        ]);

        $this->actingAs($this->clientA);

        $response = $this->get(route('reclamations.show', $pendingReclamation->id));

        $response->assertStatus(200);
        $response->assertDontSee('Réclamation Approuvée !');
        $response->assertDontSee('Vous avez reçu un code de compensation');
    }

    /**
     * Test: Rejected reclamation does NOT show reward card
     */
    public function test_rejected_reclamation_does_not_show_reward_card(): void
    {
        $rejectedReclamation = Reclamation::create([
            'user_id' => $this->clientA->id,
            'reservation_id' => $this->reservation->id,
            'title' => 'Rejected Claim',
            'description' => 'This claim will be rejected',
            'status' => 'rejetee',
            'rejected_reason' => 'Insufficient evidence',
            'rejected_at' => now(),
        ]);

        $this->actingAs($this->clientA);

        $response = $this->get(route('reclamations.show', $rejectedReclamation->id));

        $response->assertStatus(200);
        $response->assertDontSee('Réclamation Approuvée !');
    }

    /**
     * Test: Reward card displays correct discount amount for fixed value
     */
    public function test_reward_card_displays_fixed_discount_value(): void
    {
        // Create a second approved reclamation for this test
        $secondReclamation = Reclamation::create([
            'user_id' => $this->clientA->id,
            'reservation_id' => $this->reservation->id,
            'title' => 'Second Approved Claim',
            'description' => 'Another damage incident',
            'status' => 'approuvee',
            'refund_amount' => 75.50,
            'refund_approved_at' => now(),
        ]);

        // Create fixed amount compensation code for the second reclamation
        $fixedCode = CompensationCode::create([
            'code' => 'FIXED-REWARD-' . strtoupper(uniqid()),
            'type' => 'fixed',
            'value' => 75.50,
            'user_id' => $this->clientA->id,
            'reclamation_id' => $secondReclamation->id,
            'valid_from' => now(),
            'valid_until' => now()->addDays(30),
            'max_uses' => 1,
            'uses_count' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($this->clientA);

        $response = $this->get(route('reclamations.show', $secondReclamation->id));

        $response->assertStatus(200);
        // Check for fixed discount display
        $response->assertSee('de réduction forfaitaire');
        // Verify the code is displayed
        $response->assertSee($fixedCode->code);
    }

    // ===================================================================
    // FEATURE: Compensation Code Visibility
    // ===================================================================

    /**
     * Test: Client can view their own compensation code
     */
    public function test_client_can_view_own_compensation_code(): void
    {
        $this->actingAs($this->clientA);

        $response = $this->get(route('compensation-codes.show', $this->compensationCode->id));

        $response->assertStatus(200);
        $response->assertSee($this->compensationCode->code);
        $response->assertSee('Valide de');
        $response->assertSee('Jusqu');
    }

    /**
     * Test: Client can view their compensation codes list
     */
    public function test_client_can_view_compensation_codes_list(): void
    {
        $this->actingAs($this->clientA);

        $response = $this->get(route('compensation-codes.index'));

        $response->assertStatus(200);
        $response->assertSee('Mes Codes de Compensation');
        $response->assertSee($this->compensationCode->code);
    }

    /**
     * Test: Compensation codes list shows status badges (Disponible, Expiré, etc.)
     */
    public function test_compensation_codes_list_shows_status_badges(): void
    {
        $this->actingAs($this->clientA);

        $response = $this->get(route('compensation-codes.index'));

        $response->assertStatus(200);
        $response->assertSee('Disponible'); // Status for valid code
    }

    /**
     * Test: Compensation code detail shows validity dates
     */
    public function test_compensation_code_detail_shows_validity_dates(): void
    {
        $this->actingAs($this->clientA);

        $response = $this->get(route('compensation-codes.show', $this->compensationCode->id));

        $response->assertStatus(200);
        $response->assertSee($this->compensationCode->valid_from->format('d/m/Y'));
        $response->assertSee($this->compensationCode->valid_until->format('d/m/Y'));
    }

    /**
     * Test: Compensation code detail shows usage count and max uses
     */
    public function test_compensation_code_detail_shows_usage_info(): void
    {
        $this->actingAs($this->clientA);

        $response = $this->get(route('compensation-codes.show', $this->compensationCode->id));

        $response->assertStatus(200);
        $response->assertSee('Utilisations');
        $response->assertSee('0 / 1'); // uses_count / max_uses
    }

    /**
     * Test: Compensation code links back to associated reclamation
     */
    public function test_compensation_code_shows_associated_reclamation_link(): void
    {
        $this->actingAs($this->clientA);

        $response = $this->get(route('compensation-codes.show', $this->compensationCode->id));

        $response->assertStatus(200);
        $response->assertSee('Réclamation Associée');
        $response->assertSee('Réclamation #' . $this->approvedReclamation->id);
        $response->assertSee(route('reclamations.show', $this->approvedReclamation->id));
    }

    // ===================================================================
    // SECURITY: Authorization & Access Control
    // ===================================================================

    /**
     * Test: ClientB CANNOT view ClientA's reclamation (403 Forbidden)
     */
    public function test_other_client_cannot_view_anothers_reclamation(): void
    {
        $this->actingAs($this->clientB);

        $response = $this->get(route('reclamations.show', $this->approvedReclamation->id));

        $response->assertStatus(403);
    }

    /**
     * Test: ClientB CANNOT view ClientA's compensation code (403 Forbidden)
     */
    public function test_other_client_cannot_view_anothers_compensation_code(): void
    {
        $this->actingAs($this->clientB);

        $response = $this->get(route('compensation-codes.show', $this->compensationCode->id));

        $response->assertStatus(403);
    }

    /**
     * Test: ClientB CANNOT see ClientA's compensation code in their own list
     */
    public function test_client_compensation_codes_list_shows_only_own_codes(): void
    {
        // Create a code for clientB
        $clientBCode = CompensationCode::create([
            'code' => 'CLIENTB-CODE-' . strtoupper(uniqid()),
            'type' => 'percentage',
            'value' => 10,
            'user_id' => $this->clientB->id,
            'valid_from' => now(),
            'valid_until' => now()->addDays(30),
            'max_uses' => 1,
            'uses_count' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($this->clientB);

        $response = $this->get(route('compensation-codes.index'));

        $response->assertStatus(200);
        $response->assertSee($clientBCode->code);
        $response->assertDontSee($this->compensationCode->code);
    }

    /**
     * Test: Unauthenticated user cannot view reclamations
     */
    public function test_unauthenticated_user_cannot_view_reclamations(): void
    {
        $response = $this->get(route('reclamations.show', $this->approvedReclamation->id));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test: Unauthenticated user cannot view compensation codes
     */
    public function test_unauthenticated_user_cannot_view_compensation_codes(): void
    {
        $response = $this->get(route('compensation-codes.show', $this->compensationCode->id));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test: Unauthenticated user cannot access reclamations list
     */
    public function test_unauthenticated_user_cannot_view_reclamations_list(): void
    {
        $response = $this->get(route('reclamations.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test: Unauthenticated user cannot access compensation codes list
     */
    public function test_unauthenticated_user_cannot_view_compensation_codes_list(): void
    {
        $response = $this->get(route('compensation-codes.index'));

        $response->assertRedirect(route('login'));
    }

    // ===================================================================
    // FEATURE: Copy-to-Clipboard Functionality
    // ===================================================================

    /**
     * Test: Compensation code detail page shows copy button
     */
    public function test_compensation_code_shows_copy_button(): void
    {
        $this->actingAs($this->clientA);

        $response = $this->get(route('compensation-codes.show', $this->compensationCode->id));

        $response->assertStatus(200);
        $response->assertSee('Copier le code');
        $response->assertSee('navigator.clipboard');
    }

    /**
     * Test: Reclamation reward card shows copy button
     */
    public function test_reclamation_reward_card_shows_copy_button(): void
    {
        $this->actingAs($this->clientA);

        $response = $this->get(route('reclamations.show', $this->approvedReclamation->id));

        $response->assertStatus(200);
        $response->assertSee('Copier le code');
        $response->assertSee('navigator.clipboard');
    }

    // ===================================================================
    // FEATURE: Reclamation Index (List View)
    // ===================================================================

    /**
     * Test: Client can view list of their reclamations with status badges
     */
    public function test_client_reclamation_index_shows_all_own_reclamations(): void
    {
        $this->actingAs($this->clientA);

        $response = $this->get(route('reclamations.index'));

        $response->assertStatus(200);
        $response->assertSee('Mes Incidents');
        $response->assertSee($this->approvedReclamation->title);
        $response->assertSee('Approuvée'); // Status badge
    }

    /**
     * Test: Client reclamation list only shows own reclamations
     */
    public function test_client_reclamation_index_does_not_show_others_reclamations(): void
    {
        // Create reclamation for clientB
        $clientBReclamation = Reclamation::create([
            'user_id' => $this->clientB->id,
            'reservation_id' => $this->reservation->id,
            'title' => 'ClientB Reclamation',
            'description' => 'This is for another client',
            'status' => 'en_attente',
        ]);

        $this->actingAs($this->clientA);

        $response = $this->get(route('reclamations.index'));

        $response->assertStatus(200);
        $response->assertSee($this->approvedReclamation->title);
        $response->assertDontSee('ClientB Reclamation');
    }

    /**
     * Test: Reclamation list shows link to compensation code when approved
     */
    public function test_reclamation_with_compensation_code_shows_code_link(): void
    {
        $this->actingAs($this->clientA);

        // Check if show page has link to compensation code
        $response = $this->get(route('reclamations.show', $this->approvedReclamation->id));

        $response->assertStatus(200);
        $response->assertSee(route('compensation-codes.show', $this->compensationCode->id));
    }

    // ===================================================================
    // FEATURE: Expired and Invalid Codes Display
    // ===================================================================

    /**
     * Test: Expired compensation code shows unavailable state
     */
    public function test_expired_compensation_code_shows_as_unavailable(): void
    {
        $expiredCode = CompensationCode::create([
            'code' => 'EXPIRED-' . strtoupper(uniqid()),
            'type' => 'percentage',
            'value' => 10,
            'user_id' => $this->clientA->id,
            'valid_from' => now()->subDays(30),
            'valid_until' => now()->subDays(1), // Already expired
            'max_uses' => 1,
            'uses_count' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($this->clientA);

        $response = $this->get(route('compensation-codes.show', $expiredCode->id));

        $response->assertStatus(200);
        $response->assertSee('Code Non Disponible');
        $response->assertSee('expiré');
    }

    /**
     * Test: Fully used compensation code shows as unavailable
     */
    public function test_fully_used_compensation_code_shows_as_unavailable(): void
    {
        $usedCode = CompensationCode::create([
            'code' => 'USED-' . strtoupper(uniqid()),
            'type' => 'percentage',
            'value' => 10,
            'user_id' => $this->clientA->id,
            'valid_from' => now(),
            'valid_until' => now()->addDays(30),
            'max_uses' => 1,
            'uses_count' => 1, // Already fully used
            'is_active' => true,
        ]);

        $this->actingAs($this->clientA);

        $response = $this->get(route('compensation-codes.show', $usedCode->id));

        $response->assertStatus(200);
        $response->assertSee('Code Non Disponible');
        $response->assertSee('utilisé');
    }
}
