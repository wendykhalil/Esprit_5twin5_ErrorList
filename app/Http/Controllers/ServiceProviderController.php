<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceProviderRequest;
use App\Models\ServiceProvider;
use App\Services\UserRoleService;
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

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard')
                ->with('info', 'Les administrateurs gèrent les prestataires depuis le back-office.');
        }

        if (! $user->isClient()) {
            abort(403, 'Seuls les comptes client peuvent soumettre un nouveau profil prestataire.');
        }

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
    public function store(StoreServiceProviderRequest $request)
    {
        $user = $request->user();

        if ($user->serviceProvider) {
            return redirect()->route('service-providers.edit', $user->serviceProvider);
        }

        ServiceProvider::create([
            'user_id' => $user->id,
            'specialty' => $request->validated('specialty'),
            'description' => $request->validated('description'),
            'experience_years' => $request->validated('experience_years'),
            'phone' => $request->validated('phone'),
            'location' => $request->validated('location'),
            'hourly_rate' => $request->validated('hourly_rate'),
            'availability' => $request->boolean('availability'),
            'status' => 'pending',
        ]);

        return redirect()->route('service-providers.index')
            ->with('success', 'Votre profil a été soumis et est en attente de validation par un administrateur.');
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
    public function update(StoreServiceProviderRequest $request, ServiceProvider $serviceProvider)
    {
        $serviceProvider->update([
            'specialty' => $request->validated('specialty'),
            'description' => $request->validated('description'),
            'experience_years' => $request->validated('experience_years'),
            'phone' => $request->validated('phone'),
            'location' => $request->validated('location'),
            'hourly_rate' => $request->validated('hourly_rate'),
            'availability' => $request->boolean('availability'),
        ]);

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

        $user = $serviceProvider->user;
        $serviceProvider->delete();
        app(UserRoleService::class)->syncAfterProviderProfileDeleted($user);

        return redirect()->route('service-providers.index')
            ->with('success', 'Votre profil a été supprimé avec succès.');
    }
}
