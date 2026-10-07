<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceProvider;
use Illuminate\Http\Request;

class ServiceProviderController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceProvider::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('specialty')) {
            $query->where('specialty', 'like', '%' . $request->specialty . '%');
        }

        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        $serviceProviders = $query->latest()
            ->paginate(10)
            ->withQueryString();

        return view('backend.service-providers.index', compact('serviceProviders'));
    }

    public function show(ServiceProvider $serviceProvider)
    {
        $serviceProvider->load('user');
        
        return view('backend.service-providers.show', compact('serviceProvider'));
    }

    public function approve(ServiceProvider $serviceProvider)
    {
        $serviceProvider->update(['status' => 'approved']);

        return back()->with('success', 'Le profil prestataire a été approuvé.');
    }

    public function reject(ServiceProvider $serviceProvider)
    {
        $serviceProvider->update(['status' => 'rejected']);

        return back()->with('success', 'Le profil prestataire a été rejeté.');
    }
}
