<?php

namespace Tests\Feature;

use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceProviderValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guest_cannot_submit_a_provider_profile(): void
    {
        $this->post(route('service-providers.store'), $this->validPayload())
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('service_providers', 0);
    }

    public function test_non_client_cannot_submit_a_provider_profile(): void
    {
        $admin = User::factory()->admin()->create();
        $provider = User::factory()->provider()->create();

        $this->actingAs($admin)
            ->postJson(route('service-providers.store'), $this->validPayload())
            ->assertForbidden();

        $this->actingAs($provider)
            ->postJson(route('service-providers.store'), $this->validPayload())
            ->assertForbidden();

        $this->assertDatabaseCount('service_providers', 0);
    }

    public function test_missing_fields_are_rejected(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $this->actingAs($client)
            ->postJson(route('service-providers.store'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'specialty',
                'location',
                'experience_years',
                'hourly_rate',
                'description',
            ])
            ->assertJsonMissingValidationErrors(['phone']);

        $this->assertDatabaseCount('service_providers', 0);
    }

    public function test_whitespace_only_values_are_rejected(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $this->actingAs($client)
            ->from(route('service-providers.create'))
            ->post(route('service-providers.store'), [
                'specialty' => '   ',
                'location' => " \n ",
                'experience_years' => '   ',
                'hourly_rate' => '   ',
                'description' => "  \n\n  ",
                'phone' => '   ',
            ])
            ->assertRedirect(route('service-providers.create'))
            ->assertSessionHasErrors([
                'specialty',
                'location',
                'experience_years',
                'hourly_rate',
                'description',
            ])
            ->assertSessionDoesntHaveErrors(['phone']);

        $this->assertDatabaseCount('service_providers', 0);
    }

    public function test_invalid_types_and_protected_fields_are_rejected(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $this->actingAs($client)
            ->postJson(route('service-providers.store'), [
                'specialty' => ['Solaire'],
                'location' => ['Tunis'],
                'experience_years' => ['5'],
                'hourly_rate' => ['45'],
                'description' => ['texte'],
                'phone' => ['98123456'],
                'status' => 'approved',
                'user_id' => 999,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'specialty',
                'location',
                'experience_years',
                'hourly_rate',
                'description',
                'phone',
                'status',
                'user_id',
            ]);

        $this->assertDatabaseCount('service_providers', 0);
    }

    public function test_experience_and_hourly_rate_boundaries(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        foreach ([-1, 1.5, 'abc', 61, 100, '60.0'] as $experience) {
            $this->actingAs($client)
                ->postJson(route('service-providers.store'), $this->validPayload([
                    'experience_years' => $experience,
                ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['experience_years']);
        }

        foreach ([0, '0', -10, '0.00', '12.345', '1000000', 'NaN', 'Infinity'] as $rate) {
            $this->actingAs($client)
                ->postJson(route('service-providers.store'), $this->validPayload([
                    'hourly_rate' => $rate,
                ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['hourly_rate']);
        }

        $this->assertDatabaseCount('service_providers', 0);
    }

    public function test_phone_numbers_and_description_lengths(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        foreach (['abcdefgh', '12345678', '+33123456789', '98123', '11111111'] as $phone) {
            $this->actingAs($client)
                ->postJson(route('service-providers.store'), $this->validPayload([
                    'phone' => $phone,
                ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['phone']);
        }

        $this->actingAs($client)
            ->postJson(route('service-providers.store'), $this->validPayload([
                'description' => 'Trop court.',
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['description' => 'La description doit contenir au moins 30 caractères.']);

        $this->actingAs($client)
            ->postJson(route('service-providers.store'), $this->validPayload([
                'description' => str_repeat('é', 2001),
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['description']);

        $this->assertDatabaseCount('service_providers', 0);
    }

    public function test_valid_submission_stays_pending_until_an_administrator_approves_it(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($client)
            ->post(route('service-providers.store'), $this->validPayload([
                'specialty' => '  Technicien solaire  ',
                'experience_years' => '0',
                'hourly_rate' => '45,5',
                'phone' => '+216 98 123 456',
                'description' => "  Installation et maintenance de systèmes solaires.\nInterventions à domicile.  ",
            ]));

        $response->assertRedirect(route('service-providers.index'));
        $response->assertSessionHas('success', 'Votre profil a été soumis et est en attente de validation par un administrateur.');

        $profile = ServiceProvider::query()->first();
        $this->assertNotNull($profile);
        $this->assertSame($client->id, $profile->user_id);
        $this->assertSame('pending', $profile->status);
        $this->assertSame('Technicien solaire', $profile->specialty);
        $this->assertSame(0, $profile->experience_years);
        $this->assertSame('45.50', $profile->hourly_rate);
        $this->assertSame('+21698123456', $profile->phone);
        $this->assertSame('client', $client->fresh()->role);

        $this->get(route('service-providers.show', $profile))->assertNotFound();
        $this->get(route('service-providers.index'))->assertDontSee('Technicien solaire');

        $this->actingAs($admin)
            ->patch(route('admin.service-providers.approve', $profile))
            ->assertRedirect();

        $this->assertSame('approved', $profile->fresh()->status);
        $this->assertSame('provider', $client->fresh()->role);
        $this->get(route('service-providers.show', $profile))->assertOk();
    }

    public function test_optional_phone_can_be_empty_and_national_numbers_are_accepted(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $this->actingAs($client)
            ->post(route('service-providers.store'), $this->validPayload([
                'phone' => '',
                'hourly_rate' => '80.5',
            ]))
            ->assertRedirect(route('service-providers.index'));

        $profile = ServiceProvider::query()->first();
        $this->assertNull($profile->phone);
        $this->assertSame('80.50', $profile->hourly_rate);
        $this->assertSame('pending', $profile->status);
    }

    public function test_user_cannot_update_another_profile_or_its_approval_status(): void
    {
        $owner = User::factory()->provider()->create();
        $intruder = User::factory()->create(['role' => 'client']);
        $profile = ServiceProvider::factory()->create([
            'user_id' => $owner->id,
            'status' => 'approved',
            'description' => 'Installation et maintenance de systèmes solaires photovoltaïques.',
            'phone' => '+21698123456',
            'experience_years' => 4,
        ]);

        $this->actingAs($intruder)
            ->putJson(route('service-providers.update', $profile), $this->validPayload([
                'status' => 'pending',
            ]))
            ->assertForbidden();

        $this->actingAs($owner)
            ->putJson(route('service-providers.update', $profile), $this->validPayload([
                'status' => 'pending',
                'specialty' => 'Nouveau métier',
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);

        $this->assertSame('approved', $profile->fresh()->status);
        $this->assertNotSame('Nouveau métier', $profile->fresh()->specialty);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'specialty' => 'Technicien solaire',
            'location' => 'Tunis',
            'experience_years' => 5,
            'hourly_rate' => '45.00',
            'phone' => '98 123 456',
            'description' => 'Installation et maintenance de systèmes solaires photovoltaïques.',
            'availability' => '1',
        ], $overrides);
    }
}
