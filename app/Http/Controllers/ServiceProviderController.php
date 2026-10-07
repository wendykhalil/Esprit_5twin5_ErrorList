<?php

namespace App\Http\Controllers;

use App\Models\ServiceProvider;
use Illuminate\Http\Request;

class ServiceProviderController extends Controller
{
    /**
     * Display a listing of approved service providers.
     */
    public function index(Request $request)
    {
        $query = ServiceProvider::with('user')->where('status', 'approved');

        if ($request->filled('specialty')) {
            $query->where('specialty', 'like', '%' . $request->specialty . '%');
        }

        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        if ($request->filled('availability')) {
            $query->where('availability', $request->boolean('availability'));
        }

        $serviceProviders = $query->paginate(10)->withQueryString();

        return view('frontend.service-providers.index', compact('serviceProviders'));
    }

    /**
     * Show the form for creating a new service provider profile.
     */
    public function create()
    {
        $user = auth()->user();

        // Redirect if the user already has a profile
        if ($user->serviceProvider) {
            return redirect()->route('service-providers.edit', $user->serviceProvider)
                ->with('info', 'Vous avez déjà un profil de prestataire.');
        }

        return view('frontend.service-providers.create');
    }

    /**
     * Store a newly created service provider profile.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        if ($user->serviceProvider) {
            return redirect()->route('service-providers.edit', $user->serviceProvider);
        }

        $validated = $request->validate([
            'specialty' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'experience_years' => 'required|integer|min:0|max:100',
            'phone' => 'nullable|string|max:20',
            'location' => 'required|string|max:255',
            'hourly_rate' => 'required|numeric|min:0',
            'availability' => 'nullable|boolean',
        ]);

        $validated['user_id'] = $user->id;
        $validated['status'] = 'pending';
        $validated['availability'] = $request->boolean('availability');

        ServiceProvider::create($validated);

        return redirect()->route('service-providers.index')
            ->with('success', 'Votre profil a été créé et est en attente d\'approbation.');
    }

    /**
     * Display the specified service provider.
     */
    public function show(ServiceProvider $serviceProvider)
    {
        if ($serviceProvider->status !== 'approved') {
            abort(404);
        }

        $serviceProvider->load('user');

        return view('frontend.service-providers.show', compact('serviceProvider'));
    }

    /**
     * Show the form for editing the service provider profile.
     */
    public function edit(ServiceProvider $serviceProvider)
    {
        if ($serviceProvider->user_id !== auth()->id()) {
            abort(403);
        }

        return view('frontend.service-providers.edit', compact('serviceProvider'));
    }

    /**
     * Update the specified service provider profile.
     */
    public function update(Request $request, ServiceProvider $serviceProvider)
    {
        if ($serviceProvider->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'specialty' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'experience_years' => 'required|integer|min:0|max:100',
            'phone' => 'nullable|string|max:20',
            'location' => 'required|string|max:255',
            'hourly_rate' => 'required|numeric|min:0',
            'availability' => 'nullable|boolean',
        ]);

        $validated['availability'] = $request->boolean('availability');
        
        $serviceProvider->update($validated);

        return redirect()->route('service-providers.edit', $serviceProvider)
            ->with('success', 'Votre profil a été mis à jour avec succès.');
    }

    /**
     * Remove the specified service provider profile.
     */
    public function destroy(ServiceProvider $serviceProvider)
    {
        if ($serviceProvider->user_id !== auth()->id()) {
            abort(403);
        }

        if ($serviceProvider->serviceRequests()->exists()) {
            return redirect()->route('service-providers.edit', $serviceProvider)
                ->with('error', 'Ce profil ne peut pas être supprimé car il possède des demandes de service.');
        }

        $serviceProvider->delete();

        return redirect()->route('service-providers.index')
            ->with('success', 'Votre profil a été supprimé avec succès.');
    }
}
