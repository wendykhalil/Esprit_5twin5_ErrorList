<?php

namespace App\Observers;

use App\Models\ReleveEnergie;
use App\Services\BadgeAttributionService;
use App\Services\DetectionAnomalieService;

class ReleveEnergieObserver
{
    public function created(ReleveEnergie $releve): void
    {
        app(DetectionAnomalieService::class)->analyser($releve);
        $this->evaluate($releve);
    }

    public function updated(ReleveEnergie $releve): void
    {
        if (!$releve->wasChanged('statut_verification')) {
            app(DetectionAnomalieService::class)->analyser($releve);
        }
        $this->evaluate($releve);
    }

    public function deleted(ReleveEnergie $releve): void
    {
        $this->evaluate($releve);
    }

    private function evaluate(ReleveEnergie $releve): void
    {
        $releve->loadMissing('reservation.user');
        if ($releve->statut_verification !== 'suspect' && $releve->statut_verification !== 'rejete' && $releve->reservation?->user) {
            app(BadgeAttributionService::class)->evaluer($releve->reservation->user, $releve->reservation);
        }
    }
}
