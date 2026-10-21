<?php

use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\Frontend\PaymentController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\ServiceProviderController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\TechnicalServiceAIController;
use App\Http\Controllers\ReleveEnergieController;
use App\Http\Controllers\EcologyController;
use App\Http\Controllers\ConseillerEnergieController;
use App\Models\Category;
use Illuminate\Support\Facades\Route;


// --------------------------------------------------
// HOME
// --------------------------------------------------
Route::get('/', function () {
    $categories = Category::withCount('equipment')->orderBy('name')->get();

    return view('frontend.home', compact('categories'));
})->name('home');

Route::get('/ecologie', [EcologyController::class, 'index'])->name('ecology');


// --------------------------------------------------
// EQUIPMENT - PUBLIC ROUTES
// --------------------------------------------------

Route::get('/equipements', [EquipmentController::class, 'index'])
    ->name('equipments.index');


// --------------------------------------------------
// EQUIPMENT - AUTHENTICATED ROUTES
// --------------------------------------------------

Route::middleware('auth')->group(function () {

    // Edit (la création se fait uniquement dans le back-office /admin/equipements/create)
    Route::get('/equipements/{equipment}/edit', [EquipmentController::class, 'edit'])
        ->whereNumber('equipment')
        ->name('equipments.edit');

    Route::put('/equipements/{equipment}', [EquipmentController::class, 'update'])
        ->whereNumber('equipment')
        ->name('equipments.update');

    // Delete
    Route::delete('/equipements/{equipment}', [EquipmentController::class, 'destroy'])
        ->whereNumber('equipment')
        ->name('equipments.destroy');
});




// SolarShare AI Equipment Recommendations
Route::get(
    '/equipements/recommandations-ia',
    [\App\Http\Controllers\EquipmentRecommendationController::class, 'index']
)->name('equipments.ai.recommend.index');

Route::post(
    '/equipements/recommandations-ia',
    [\App\Http\Controllers\EquipmentRecommendationController::class, 'recommend']
)
    ->middleware(['auth', 'throttle:5,1'])
    ->name('equipments.ai.recommend.store');



// --------------------------------------------------
// EQUIPMENT DETAILS
// IMPORTANT: keep this after /create and /edit
// --------------------------------------------------

Route::get('/equipements/{equipment}', [EquipmentController::class, 'show'])
    ->whereNumber('equipment')
    ->name('equipments.show');

Route::get('/equipements/{equipment}/availability', [EquipmentController::class, 'availability'])
    ->whereNumber('equipment')
    ->name('equipments.availability');
// SolarShare AI Equipment Chat
Route::post(
    '/equipements/{equipment}/ai/chat',
    [\App\Http\Controllers\EquipmentChatAIController::class, 'chat']
)
    ->middleware(['auth', 'throttle:10,1'])
    ->whereNumber('equipment')
    ->name('equipments.ai.chat');


// --------------------------------------------------
// SOLARSHARE STATIC PAGES
// --------------------------------------------------

Route::get('/a-propos', function () {
    return view('frontend.about');
})->name('about');


Route::get('/comment-ca-marche', function () {
    return view('frontend.how-it-works');
})->name('how-it-works');


Route::get('/contact', function () {
    return view('frontend.contact');
})->name('contact');


// --------------------------------------------------
// RENTALS
// --------------------------------------------------

Route::get('/mes-reservations', function () {
    return view('frontend.rentals.index');
})
    ->middleware('auth')
    ->name('rentals.index');


// --------------------------------------------------
// RESERVATIONS & INSPECTIONS
// --------------------------------------------------

Route::get('/reservations', [ReservationController::class, 'index'])
    ->middleware('auth')
    ->name('reservations.index');

Route::middleware('auth')->group(function () {
    Route::resource('reservations', ReservationController::class)
        ->except(['index']);
    Route::patch('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])
        ->name('reservations.cancel');
    Route::resource('inspections', InspectionController::class);
    Route::resource('impact', ReleveEnergieController::class)
        ->parameters(['impact' => 'releveEnergie'])
        ->names('impact');
    Route::get('/conseiller-energie', [ConseillerEnergieController::class, 'index'])->name('energy-advisor');
    Route::post('/conseiller-energie/recommandations', [ConseillerEnergieController::class, 'recommend'])->name('energy-advisor.recommend');
    Route::post('/impact/bilan-ia', [ConseillerEnergieController::class, 'bilan'])->name('impact.ai-summary');
});


// --------------------------------------------------
// PAYMENTS
// --------------------------------------------------

Route::middleware('auth')->group(function () {

    Route::get('/paiement/creer', [PaymentController::class, 'create'])
        ->name('payments.create');

    Route::post('/paiement', [PaymentController::class, 'store'])
        ->name('payments.store');

    Route::get('/paiement/{payment}', [PaymentController::class, 'show'])
        ->name('payments.show');

    Route::get('/mes-paiements', [PaymentController::class, 'history'])
        ->name('payments.history');

    Route::get('/mes-factures', [PaymentController::class, 'invoices'])
        ->name('payments.invoices');

    Route::get('/mes-paiements/{payment}/facture', [PaymentController::class, 'invoice'])
        ->name('payments.invoice');
});


// --------------------------------------------------
// CONTRACTS
// --------------------------------------------------

Route::middleware('auth')->group(function () {
    Route::get('/contracts', [ContractController::class, 'index'])
        ->name('contracts.index');

    Route::get('/contracts/{contract}', [ContractController::class, 'show'])
        ->name('contracts.show');

    Route::post('/contracts/{contract}/accept', [ContractController::class, 'accept'])
        ->name('contracts.accept');

    Route::get('/contracts/{contract}/print', [ContractController::class, 'print'])
        ->name('contracts.print');

    Route::get('/contracts/{contract}/download', [ContractController::class, 'download'])
        ->name('contracts.download');
});


// --------------------------------------------------
// DELIVERIES
// --------------------------------------------------

use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\ReclamationController;
use App\Http\Controllers\CompensationCodeController;

Route::middleware('auth')->group(function () {
    Route::get('/livraisons', [DeliveryController::class, 'index'])
        ->name('deliveries.index');

    Route::get('/livraisons/{delivery}', [DeliveryController::class, 'show'])
        ->name('deliveries.show');

    // Client confirms receipt of equipment
    Route::post('/livraisons/{delivery}/confirm-receipt', [DeliveryController::class, 'confirmReceipt'])
        ->name('deliveries.confirmReceipt');
});


// --------------------------------------------------
// RECLAMATIONS (CLIENT)
// --------------------------------------------------

use App\Http\Controllers\AIReclamationAssistantController;

Route::middleware('auth')->group(function () {
    // AI-powered incident description generation
    Route::post('/incidents/generate-description', [IncidentController::class, 'generateDescription'])
        ->name('incidents.generate-description');
});

Route::middleware('auth')->prefix('reclamations')->name('reclamations.')->group(function () {
    Route::get('/', [ReclamationController::class, 'index'])->name('index');
    Route::get('/create', [ReclamationController::class, 'create'])->name('create');
    Route::post('/', [ReclamationController::class, 'store'])->name('store');
    Route::get('/{reclamation}', [ReclamationController::class, 'show'])->name('show');
    Route::get('/{reclamation}/edit', [ReclamationController::class, 'edit'])->name('edit');
    Route::put('/{reclamation}', [ReclamationController::class, 'update'])->name('update');
    Route::delete('/{reclamation}', [ReclamationController::class, 'destroy'])->name('destroy');
});

// AI Reclamation Assistant routes
Route::middleware('auth')->prefix('reclamations/ai-assistant')->name('reclamations.ai-assistant.')->group(function () {
    Route::get('/reservations', [AIReclamationAssistantController::class, 'getReservations'])
        ->name('reservations');
    Route::post('/generate-reclamation-details', [AIReclamationAssistantController::class, 'generateReclamationDetails'])
        ->name('generate-reclamation-details');
    Route::post('/generate-description', [AIReclamationAssistantController::class, 'generateDescription'])
        ->name('generate-description');
    Route::post('/upload-evidence', [AIReclamationAssistantController::class, 'uploadEvidence'])
        ->name('upload-evidence');
    Route::post('/submit', [AIReclamationAssistantController::class, 'submitReclamation'])
        ->name('submit');
});


// --------------------------------------------------
// COMPENSATION CODES (CLIENT)
// --------------------------------------------------

Route::middleware('auth')->prefix('codes-compensation')->name('compensation-codes.')->group(function () {
    Route::get('/', [CompensationCodeController::class, 'index'])->name('index');
    Route::get('/{compensationCode}', [CompensationCodeController::class, 'show'])->name('show');
    Route::post('/validate', [CompensationCodeController::class, 'validate'])->name('validate');
});


// --------------------------------------------------
// SOLARSHARE DASHBOARD REDIRECT
// --------------------------------------------------

Route::get('/dashboard', function () {

    $user = auth()->user();

    // Admin -> Existing administration dashboard
    if ($user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    // Client and Provider -> SolarShare homepage
    return redirect()->route('home');

})
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// --------------------------------------------------
// SUPPORT TICKETS (CLIENT)
// --------------------------------------------------

Route::middleware('auth')->prefix('support')->name('support.tickets.')->group(function () {
    Route::get('/', [SupportTicketController::class, 'index'])->name('index');
    Route::get('/create', [SupportTicketController::class, 'create'])->name('create');
    Route::post('/', [SupportTicketController::class, 'store'])->name('store');
    Route::get('/{supportTicket}', [SupportTicketController::class, 'show'])->name('show');
    Route::post('/{supportTicket}/replies', [SupportTicketController::class, 'storeReply'])
        ->name('replies.store');
    Route::post('/{supportTicket}/replies/{reply}/accept', [SupportTicketController::class, 'acceptSolution'])
        ->name('replies.accept');
    Route::post('/{supportTicket}/reopen', [SupportTicketController::class, 'reopen'])
        ->name('reopen');
    Route::post('/{supportTicket}/satisfaction', [SupportTicketController::class, 'rate'])
        ->name('satisfaction');
});

// --------------------------------------------------
// PROFILE
// --------------------------------------------------

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::delete('/profile/photo', [ProfileController::class, 'deleteProfilePhoto'])
        ->name('profile.photo.delete');

    Route::get('/profil', [ProfileController::class, 'edit'])
        ->name('profile');
});


// --------------------------------------------------
// NOTIFICATIONS
// --------------------------------------------------

use App\Http\Controllers\NotificationController;

Route::middleware('auth')->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/{notification}', [NotificationController::class, 'show'])->name('show');
    Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
    Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');

    // API endpoints for AJAX/real-time updates
    Route::get('/api/unread', [NotificationController::class, 'unread'])->name('api.unread');
    Route::get('/api/count', [NotificationController::class, 'count'])->name('api.count');
});


// --------------------------------------------------
// TECHNICAL SERVICES: SERVICE PROVIDERS
// --------------------------------------------------

Route::get('/services', [ServiceProviderController::class, 'index'])
    ->name('service-providers.index');

Route::middleware('auth')->group(function () {
    Route::middleware('client')->group(function () {
        Route::get('/services/create', [ServiceProviderController::class, 'create'])
            ->name('service-providers.create');

        Route::post('/services', [ServiceProviderController::class, 'store'])
            ->name('service-providers.store');
    });

    Route::get('/services/{serviceProvider}/edit', [ServiceProviderController::class, 'edit'])
        ->name('service-providers.edit');

    Route::put('/services/{serviceProvider}', [ServiceProviderController::class, 'update'])
        ->name('service-providers.update');

    Route::delete('/services/{serviceProvider}', [ServiceProviderController::class, 'destroy'])
        ->name('service-providers.destroy');

    // Technical Services: AI Pre-diagnosis (nom unique unifié)
    Route::post('/services/ai-diagnose', [TechnicalServiceAIController::class, 'diagnose'])
        ->name('service-requests.ai-diagnose');

    // Technical Services: Service Requests
    Route::get('/services/{serviceProvider}/request', [ServiceRequestController::class, 'create'])
        ->name('service-requests.create');
    Route::post('/services/{serviceProvider}/request', [ServiceRequestController::class, 'store'])
        ->name('service-requests.store');
    Route::get('/my-service-requests', [ServiceRequestController::class, 'index'])
        ->name('service-requests.index');
    Route::get('/my-service-requests/{serviceRequest}', [ServiceRequestController::class, 'show'])
        ->name('service-requests.show');
    Route::delete('/my-service-requests/{serviceRequest}', [ServiceRequestController::class, 'destroy'])
        ->name('service-requests.destroy');

    // Technical Services: Provider Side Requests (rôle prestataire approuvé)
    Route::middleware('provider')->prefix('provider')->group(function () {
        Route::get('/service-requests', [ServiceRequestController::class, 'providerIndex'])
            ->name('provider.service-requests.index');
        Route::get('/service-requests/{serviceRequest}', [ServiceRequestController::class, 'providerShow'])
            ->name('provider.service-requests.show');
        Route::patch('/service-requests/{serviceRequest}/accept', [ServiceRequestController::class, 'accept'])
            ->name('provider.service-requests.accept');
        Route::patch('/service-requests/{serviceRequest}/reject', [ServiceRequestController::class, 'reject'])
            ->name('provider.service-requests.reject');
        Route::patch('/service-requests/{serviceRequest}/start', [ServiceRequestController::class, 'start'])
            ->name('provider.service-requests.start');
        Route::patch('/service-requests/{serviceRequest}/complete', [ServiceRequestController::class, 'complete'])
            ->name('provider.service-requests.complete');
    });
});

Route::get('/services/{serviceProvider}', [ServiceProviderController::class, 'show'])
    ->name('service-providers.show');





// --------------------------------------------------
// ADMIN ROUTES
// --------------------------------------------------

require __DIR__.'/admin.php';


// --------------------------------------------------
// BREEZE AUTHENTICATION ROUTES
// --------------------------------------------------

require __DIR__.'/auth.php';