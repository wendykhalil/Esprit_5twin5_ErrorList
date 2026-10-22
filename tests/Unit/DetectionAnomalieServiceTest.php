<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\ReleveEnergie;
use App\Models\Reservation;
use App\Models\User;
use App\Services\DetectionAnomalieService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DetectionAnomalieServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_marks_a_releve_over_the_reservation_duration_as_suspect(): void
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create([
            'user_id' => User::factory()->provider()->create()->id,
            'category_id' => Category::create(['name' => 'Solaire'])->id,
            'power' => 500,
        ]);
        $reservation = Reservation::factory()->create([
            'user_id' => $user->id, 'equipment_id' => $equipment->id,
            'date_debut' => '2026-10-01', 'date_fin' => '2026-10-01',
        ]);
        $releve = ReleveEnergie::factory()->create([
            'reservation_id' => $reservation->id, 'heures_utilisation' => 30,
            'kwh_estimes' => 1,
            'date_releve' => '2026-10-01',
        ]);

        app(DetectionAnomalieService::class)->analyser($releve);

        $this->assertSame('suspect', $releve->fresh()->statut_verification);
        $this->assertStringContainsString('durée', $releve->fresh()->motif_suspicion);
    }

    public function test_it_keeps_a_releve_within_the_reservation_duration_normal(): void
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create([
            'user_id' => User::factory()->provider()->create()->id,
            'category_id' => Category::create(['name' => 'Solaire'])->id,
            'power' => 500,
        ]);
        $reservation = Reservation::factory()->create([
            'user_id' => $user->id, 'equipment_id' => $equipment->id,
            'date_debut' => '2026-10-01', 'date_fin' => '2026-10-01',
        ]);
        $releve = ReleveEnergie::factory()->create([
            'reservation_id' => $reservation->id, 'heures_utilisation' => 3,
            'kwh_estimes' => 1,
            'date_releve' => '2026-10-01',
        ]);

        app(DetectionAnomalieService::class)->analyser($releve);

        $this->assertNotSame('suspect', $releve->fresh()->statut_verification);
    }
}
