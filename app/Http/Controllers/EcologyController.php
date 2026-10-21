<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\ReleveEnergie;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EcologyController extends Controller
{
    public function index(): View
    {
        return view('frontend.ecology', [
            'stats' => [
                'kwh' => (float) ReleveEnergie::whereIn('statut_verification', ['normal', 'valide'])->sum('kwh_estimes'),
                'co2' => (float) ReleveEnergie::whereIn('statut_verification', ['normal', 'valide'])->sum('co2_evite_kg'),
                'reservations' => Reservation::where('statut', 'terminee')->count(),
                'badges' => DB::table('badge_user')->count(),
            ],
            'badges' => Badge::query()->where('actif', true)->orderBy('seuil')->get(),
            'equivalences' => config('solarshare.ecology_equivalences'),
        ]);
    }
}
