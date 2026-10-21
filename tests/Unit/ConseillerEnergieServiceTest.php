<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\User;
use App\Services\ConseillerEnergieService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ConseillerEnergieServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_ignores_invalid_llm_equipment_ids(): void
    {
        Http::fake(['*' => Http::response(['message' => ['content' => '{"recommandations":[{"equipment_id":999,"justification":"invalide","kwh_estimes":4}]}']], 200)]);
        $equipment = Equipment::factory()->create([
            'user_id' => User::factory()->provider()->create()->id,
            'category_id' => Category::create(['name' => 'Solaire'])->id,
            'availability' => true,
            'name' => 'Panneau solaire',
        ]);

        $result = app(ConseillerEnergieService::class)->recommander('solaire');

        $this->assertSame([], $result['recommandations']);
        $this->assertNotContains($equipment->id, array_column($result['recommandations'], 'equipment_id'));
    }
}
