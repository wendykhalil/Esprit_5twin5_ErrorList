<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEquipmentStoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_admin_equipment_store_validates_on_server(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->from(route('admin.equipments.create'))
            ->post(route('admin.equipments.store'), []);

        $response
            ->assertRedirect(route('admin.equipments.create'))
            ->assertSessionHasErrors(['category_id', 'name', 'description', 'condition', 'price_per_day', 'location', 'status']);
    }
}
