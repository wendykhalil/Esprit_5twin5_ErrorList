<?php

namespace App\Observers;

use App\Models\ReleveEnergie;
use App\Services\BadgeAttributionService;

class ReleveEnergieObserver
{
    public function created(ReleveEnergie $releve): void
    {
        $this->evaluate($releve);
    }

    public function updated(ReleveEnergie $releve): void
    {
        $this->evaluate($releve);
    }

    public function deleted(ReleveEnergie $releve): void
    {
        $this->evaluate($releve);
    }

    private function evaluate(ReleveEnergie $releve): void
    {
        $releve->loadMissing('reservation.user');
        if ($releve->reservation?->user) {
            app(BadgeAttributionService::class)->evaluer($releve->reservation->user, $releve->reservation);
        }
    }
}
