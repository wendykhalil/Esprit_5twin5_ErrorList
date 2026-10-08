<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReservationAdminRequest;
use App\Models\Equipment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $query = Reservation::with(['user', 'equipment', 'inspections']);

        if ($request->filled('search')) {
            $search = $request->string('search');

            $query->where(function ($query) use ($search) {
                $query->whereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('equipment', fn ($equipment) => $equipment->where('name', 'like', "%{$search}%"));
            });
        }

        $selectedStatus = $request->input('status', 'Tous');
        if ($selectedStatus !== 'Tous') {
            $query->where('statut', $selectedStatus);
        }

        $reservations = $query->latest()->paginate(10)->withQueryString();

        return view('backend.reservations.index', [
            'reservations' => $reservations,
            'statuses' => [
                'Tous' => 'Tous',
                'en_attente' => 'En attente',
                'en_cours' => 'En cours',
                'confirmee' => 'Confirmée',
                'terminee' => 'Terminée',
                'litige' => 'Litige',
                'annulee' => 'Annulée',
            ],
            'search' => $request->input('search', ''),
            'selectedStatus' => $selectedStatus,
        ]);
    }

    public function create()
    {
        return view('backend.reservations.create', $this->formData());
    }

    public function store(ReservationAdminRequest $request)
    {
        $data = $request->validated();
        $data['prix_total'] = $this->calculatePrice($data);
        Reservation::create($data);

        return redirect()->route('admin.reservations.index')->with('success', 'Réservation créée.');
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['user', 'equipment', 'inspections']);

        return view('backend.reservations.show', compact('reservation'));
    }

    public function edit(Reservation $reservation)
    {
        return view('backend.reservations.edit', $this->formData() + compact('reservation'));
    }

    public function update(ReservationAdminRequest $request, Reservation $reservation)
    {
        $data = $request->validated();
        $data['prix_total'] = $this->calculatePrice($data);

        $reservation->update($data);

        return redirect()->route('admin.reservations.show', $reservation)->with('success', 'Réservation mise à jour.');
    }

    public function destroy(Reservation $reservation)
    {
        $reservation->delete();

        return redirect()->route('admin.reservations.index')->with('success', 'Réservation supprimée.');
    }

    private function formData(): array
    {
        return [
            'users' => User::orderBy('name')->get(),
            'equipments' => Equipment::orderBy('name')->get(),
            'statuses' => Reservation::STATUTS,
        ];
    }

    private function calculatePrice(array $data): float
    {
        $equipment = Equipment::findOrFail($data['equipment_id']);
        $days = Carbon::parse($data['date_debut'])->diffInDays(Carbon::parse($data['date_fin']));

        return round(max($days, 1) * ($equipment->price_per_day ?? $equipment->prix_jour ?? $equipment->price ?? 0), 2);
    }
}