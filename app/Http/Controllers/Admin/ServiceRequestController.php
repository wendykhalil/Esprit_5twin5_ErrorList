<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    /**
     * Display a listing of all service requests with KPIs and filters.
     */
    public function index(Request $request)
    {
        $query = ServiceRequest::with(['user', 'serviceProvider.user', 'equipment'])
            ->latest();

        // 1. Calcul des KPIs en 1 seule requête SQL optimisée
        $kpis = ServiceRequest::query()
            ->selectRaw("
                count(*) as total,
                count(case when status = 'pending' then 1 end) as pending_count,
                count(case when status = 'accepted' then 1 end) as accepted_count,
                count(case when status = 'in_progress' then 1 end) as in_progress_count,
                count(case when status = 'completed' then 1 end) as completed_count,
                count(case when status = 'rejected' then 1 end) as rejected_count,
                count(case when status = 'cancelled' then 1 end) as cancelled_count,
                coalesce(sum(case when status = 'completed' then estimated_price end), 0) as total_completed_estimated_amount
            ")
            ->first();

        // 2. Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('service_provider_id')) {
            $query->where('service_provider_id', $request->service_provider_id);
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                  ->orWhere('description', 'like', $search)
                  ->orWhere('address', 'like', $search)
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', $search)
                         ->orWhere('email', 'like', $search);
                  })
                  ->orWhereHas('serviceProvider.user', function ($pq) use ($search) {
                      $pq->where('name', 'like', $search);
                  });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('requested_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('requested_date', '<=', $request->date_to);
        }

        $serviceRequests = $query->paginate(10)->withQueryString();

        $providers = ServiceProvider::with('user')
            ->where('status', 'approved')
            ->get();

        return view('backend.service-requests.index', compact('serviceRequests', 'kpis', 'providers'));
    }

    /**
     * Display the specified service request details for admin.
     */
    public function show(ServiceRequest $serviceRequest)
    {
        $serviceRequest->load(['user', 'serviceProvider.user', 'equipment', 'cancelledBy']);

        return view('backend.service-requests.show', compact('serviceRequest'));
    }

    /**
     * Cancel an active service request (from pending, accepted, or in_progress).
     */
    public function cancel(Request $request, ServiceRequest $serviceRequest)
    {
        if (!in_array($serviceRequest->status, ['pending', 'accepted', 'in_progress'], true)) {
            return back()->with('error', "Cette intervention ne peut plus être annulée car son statut actuel est '{$serviceRequest->status}'.");
        }

        $validated = $request->validate([
            'cancellation_reason' => 'required|string|min:5|max:1000',
        ]);

        $serviceRequest->update([
            'status' => 'cancelled',
            'cancellation_reason' => $validated['cancellation_reason'],
            'cancelled_at' => now(),
            'cancelled_by' => auth()->id(),
        ]);

        return redirect()->route('admin.service-requests.show', $serviceRequest)
            ->with('success', "L'intervention #{$serviceRequest->id} a été annulée avec succès.");
    }

    /**
     * Emergency completion for an intervention that is in_progress.
     */
    public function complete(ServiceRequest $serviceRequest)
    {
        if ($serviceRequest->status !== 'in_progress') {
            return back()->with('error', "Seule une intervention en cours ('in_progress') peut être clôturée administrativement.");
        }

        $serviceRequest->update([
            'status' => 'completed',
        ]);

        return redirect()->route('admin.service-requests.show', $serviceRequest)
            ->with('success', "L'intervention #{$serviceRequest->id} a été marquée comme terminée.");
    }
}
