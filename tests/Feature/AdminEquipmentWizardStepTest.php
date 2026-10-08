<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEquipmentWizardStepTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_wizard_step_one_requires_core_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.equipments.create.validate-step'), [
                'wizard_step' => 1,
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'category_id', 'description', 'condition']);
    }

    public function test_wizard_step_one_passes_with_valid_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $categoryId = Category::query()->create([
            'name' => 'Panneaux',
            'description' => 'Catégorie test',
        ])->id;

        $response = $this->actingAs($admin)
            ->postJson(route('admin.equipments.create.validate-step'), [
                'wizard_step' => 1,
                'name' => 'Panneau test',
                'category_id' => $categoryId,
                'description' => 'Description suffisamment longue pour passer la validation.',
                'condition' => 'good',
            ]);

        $response->assertOk()->assertJson(['ok' => true]);
    }
}
