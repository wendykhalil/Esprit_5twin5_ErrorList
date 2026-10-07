<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\ServiceProvider;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ServiceRequestController extends Controller
{
    public function index()
    {
        $serviceRequests = ServiceRequest::with(['serviceProvider.user', 'equipment'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('frontend.service-requests.index', compact('serviceRequests'));
    }

    public function create(ServiceProvider $serviceProvider)
    {
        if ($serviceProvider->status !== 'approved' || !$serviceProvider->availability) {
            return redirect()->route('service-providers.show', $serviceProvider)
                ->with('error', 'Ce prestataire n\'est pas disponible pour le moment.');
        }

        if ($serviceProvider->user_id === Auth::id()) {
            return redirect()->route('service-providers.show', $serviceProvider)
                ->with('error', 'Vous ne pouvez pas demander une intervention à vous-même.');
        }

        $equipments = Equipment::where('user_id', Auth::id())->get();

        return view('frontend.service-requests.create', compact('serviceProvider', 'equipments'));
    }

    public function store(Request $request, ServiceProvider $serviceProvider)
    {
        if ($serviceProvider->status !== 'approved' || !$serviceProvider->availability) {
            return redirect()->route('service-providers.show', $serviceProvider)
                ->with('error', 'Ce prestataire n\'est pas disponible pour le moment.');
        }

        if ($serviceProvider->user_id === Auth::id()) {
            return redirect()->route('service-providers.show', $serviceProvider)
                ->with('error', 'Vous ne pouvez pas demander une intervention à vous-même.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'requested_date' => 'required|date|after_or_equal:today',
            'address' => 'required|string|max:255',
            'equipment_id' => [
                'nullable',
                Rule::exists('equipment', 'id')
                    ->where(fn ($query) => $query->where('user_id', Auth::id())),
            ],
        ]);

        ServiceRequest::create([
            'user_id' => Auth::id(),
            'service_provider_id' => $serviceProvider->id,
            'equipment_id' => $validated['equipment_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'requested_date' => $validated['requested_date'],
            'address' => $validated['address'],
            'status' => 'pending',
            'estimated_price' => null,
        ]);

        return redirect()->route('service-requests.index')
            ->with('success', 'Votre demande d\'intervention a été envoyée avec succès.');
    }

    public function show(ServiceRequest $serviceRequest)
    {
        if ($serviceRequest->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé.');
        }

        $serviceRequest->load(['serviceProvider.user', 'equipment']);

        return view('frontend.service-requests.show', compact('serviceRequest'));
    }

    public function destroy(ServiceRequest $serviceRequest)
    {
        if ($serviceRequest->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé.');
        }

        if ($serviceRequest->status !== 'pending') {
            return back()->with('error', 'Vous ne pouvez annuler qu\'une demande en attente.');
        }

        $serviceRequest->delete();

        return redirect()->route('service-requests.index')
            ->with('success', 'Votre demande d\'intervention a été annulée.');
    }
}
