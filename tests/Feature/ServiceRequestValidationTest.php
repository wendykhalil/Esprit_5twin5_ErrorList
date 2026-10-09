<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceRequestValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guest_cannot_submit_a_service_request(): void
    {
        $provider = $this->provider();

        $this->post(route('service-requests.store', $provider), $this->validPayload())
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('service_requests', 0);
    }

    public function test_missing_fields_are_rejected(): void
    {
        [$client, $provider] = $this->clientAndProvider();

        $this->actingAs($client)
            ->postJson(route('service-requests.store', $provider), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'requested_date', 'address', 'description'])
            ->assertJsonMissingValidationErrors(['equipment_id']);

        $this->assertDatabaseCount('service_requests', 0);
    }

    public function test_whitespace_and_invalid_values_are_rejected(): void
    {
        [$client, $provider] = $this->clientAndProvider();

        $this->actingAs($client)
            ->from(route('service-requests.create', $provider))
            ->post(route('service-requests.store', $provider), [
                'title' => '   ',
                'requested_date' => 'demain',
                'address' => '  ab  ',
                'description' => "  \n trop court \n ",
                'equipment_id' => ['not-an-id'],
            ])
            ->assertRedirect(route('service-requests.create', $provider))
            ->assertSessionHasErrors(['title', 'requested_date', 'address', 'description', 'equipment_id']);

        $this->assertDatabaseCount('service_requests', 0);
    }

    public function test_past_date_and_foreign_equipment_are_rejected(): void
    {
        [$client, $provider] = $this->clientAndProvider();
        $other = User::factory()->create();
        $foreignEquipment = $this->equipmentFor($other);

        $this->actingAs($client)
            ->postJson(route('service-requests.store', $provider), $this->validPayload([
                'requested_date' => now()->subDay()->toDateString(),
                'equipment_id' => $foreignEquipment->id,
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['requested_date', 'equipment_id']);

        $this->assertDatabaseCount('service_requests', 0);
    }

    public function test_protected_fields_are_rejected_and_nothing_is_saved(): void
    {
        [$client, $provider] = $this->clientAndProvider();
        $otherProvider = $this->provider();

        $this->actingAs($client)
            ->postJson(route('service-requests.store', $provider), $this->validPayload([
                'status' => 'accepted',
                'estimated_price' => '150.00',
                'user_id' => $otherProvider->user_id,
                'service_provider_id' => $otherProvider->id,
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status', 'estimated_price', 'user_id', 'service_provider_id']);

        $this->assertDatabaseCount('service_requests', 0);
    }

    public function test_a_client_cannot_request_their_own_profile(): void
    {
        $owner = User::factory()->create();
        $provider = $this->provider($owner);

        $this->actingAs($owner)
            ->post(route('service-requests.store', $provider), $this->validPayload())
            ->assertRedirect(route('service-providers.show', $provider))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('service_requests', 0);
    }

    public function test_an_unavailable_provider_cannot_receive_a_request(): void
    {
        [$client, $provider] = $this->clientAndProvider();
        $provider->update(['availability' => false]);

        $this->actingAs($client)
            ->post(route('service-requests.store', $provider), $this->validPayload())
            ->assertRedirect(route('service-providers.show', $provider))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('service_requests', 0);
    }

    public function test_a_valid_request_stays_pending_without_a_price(): void
    {
        [$client, $provider] = $this->clientAndProvider();
        $equipment = $this->equipmentFor($client);

        $this->actingAs($client)
            ->post(route('service-requests.store', $provider), $this->validPayload([
                'title' => '  Panne onduleur  ',
                'equipment_id' => (string) $equipment->id,
            ]))
            ->assertRedirect(route('service-requests.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('service_requests', [
            'user_id' => $client->id,
            'service_provider_id' => $provider->id,
            'equipment_id' => $equipment->id,
            'title' => 'Panne onduleur',
            'status' => 'pending',
            'estimated_price' => null,
        ]);
    }

    public function test_equipment_can_be_omitted(): void
    {
        [$client, $provider] = $this->clientAndProvider();

        $this->actingAs($client)
            ->post(route('service-requests.store', $provider), $this->validPayload([
                'equipment_id' => '',
            ]))
            ->assertRedirect(route('service-requests.index'));

        $this->assertDatabaseHas('service_requests', [
            'user_id' => $client->id,
            'service_provider_id' => $provider->id,
            'equipment_id' => null,
            'status' => 'pending',
        ]);
    }

    private function clientAndProvider(): array
    {
        return [User::factory()->create(), $this->provider()];
    }

    private function provider(?User $user = null): ServiceProvider
    {
        return ServiceProvider::factory()->create([
            'user_id' => $user?->id ?? User::factory(),
            'status' => 'approved',
            'availability' => true,
            'description' => 'Technicien solaire disponible pour les interventions à domicile et le diagnostic des installations.',
        ]);
    }

    private function equipmentFor(User $user): Equipment
    {
        $category = Category::query()->create([
            'name' => 'Panneaux',
            'description' => 'Catégorie de test',
        ]);

        return Equipment::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Panne onduleur',
            'requested_date' => now()->addDay()->toDateString(),
            'address' => '12 rue de la République, Tunis',
            'description' => 'L\'onduleur s\'arrête dès que la production dépasse 2 kW. Merci de prévoir un diagnostic sur place.',
            'equipment_id' => '',
        ], $overrides);
    }
}
