<?php

namespace App\Observers;

use App\Models\Inspection;
use App\Services\BadgeAttributionService;

class InspectionObserver
{
    public function created(Inspection $inspection): void
    {
        $this->evaluate($inspection);
    }

    public function updated(Inspection $inspection): void
    {
        $this->evaluate($inspection);
    }

    private function evaluate(Inspection $inspection): void
    {
        if ($inspection->type !== 'retour') {
            return;
        }

        $inspection->loadMissing('reservation.user');
        if ($inspection->reservation?->user) {
            app(BadgeAttributionService::class)->evaluer($inspection->reservation->user, $inspection->reservation);
        }
    }
}
