<?php

use App\Http\Controllers\Admin\CancellationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventCategoryController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PayoutController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Organizer\DashboardController as OrganizerDashboardController;
use App\Http\Controllers\Organizer\ScannerController;
use App\Http\Controllers\Organizer\SettingsController as OrganizerSettingsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\EventCatalogController;
use App\Http\Controllers\User\CheckoutController;
use App\Http\Controllers\User\PesananController;
use App\Http\Controllers\Webhook\PaymentWebhookController;
use App\Models\Event;
use App\Models\EventCategory;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $popularEvents = Event::with(['category', 'ticketCategories' => function ($q) {
        $q->where('is_active', true);
    }])
        ->where('status', 'published')
        ->orderByDesc('is_featured')
        ->orderBy('event_date')
        ->take(3)
        ->get();

    $eventCategories = EventCategory::orderBy('created_at', 'desc')->take(6)->get();

    return view('public.welcome', compact('popularEvents', 'eventCategories'));
})->name('landing');

Route::get('/events', [EventCatalogController::class, 'index'])->name('events.index');
Route::get('/events/{slug}', [EventCatalogController::class, 'show'])->name('events.show');

Route::get('/dashboard', function () {
    $role = request()->user()->role->value ?? request()->user()->role;

    if ($role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($role === 'organizer') {
        return redirect()->route('organizer.dashboard');
    }

    return redirect()->route('profile.index');
})->middleware(['auth', 'verified', 'role:admin,organizer,user'])->name('dashboard');

// Admin Routes
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // User Management
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('users/{user}/approve-organizer', [UserController::class, 'approveOrganizer'])->name('users.approve-organizer');
    Route::post('users/{user}/reject-organizer', [UserController::class, 'rejectOrganizer'])->name('users.reject-organizer');

    // Event Approval & Intelligence
    Route::get('events', [EventController::class, 'index'])->name('events.index');
    Route::get('events/{event}', [EventController::class, 'show'])->name('events.show');
    Route::put('events/{event}/status', [EventController::class, 'updateStatus'])->name('events.update-status');
    Route::patch('events/{event}/toggle-featured', [EventController::class, 'toggleFeatured'])->name('events.toggle-featured');

    // Payout Management
    Route::get('payouts', [PayoutController::class, 'index'])->name('payouts.index');
    Route::get('payouts/audit-logs', [PayoutController::class, 'auditLogs'])->name('payouts.audit-logs');
    Route::get('payouts/{payout}', [PayoutController::class, 'show'])->name('payouts.show');
    Route::post('payouts/initialize/{event}', [PayoutController::class, 'initializeFinalPayout'])->name('payouts.initialize');
    Route::put('payouts/{payout}/approve', [PayoutController::class, 'approve'])->name('payouts.approve');
    Route::put('payouts/{payout}/approve-advance', [PayoutController::class, 'approveAdvance'])->name('payouts.approve-advance');
    Route::put('payouts/{payout}/reject-advance', [PayoutController::class, 'rejectAdvance'])->name('payouts.reject-advance');
    Route::post('payouts/{payout}/disburse', [PayoutController::class, 'disburse'])->name('payouts.disburse');

    // Cancellation Review Queue
    Route::get('cancellations', [CancellationController::class, 'index'])->name('cancellations.index');
    Route::put('cancellations/{cancellationRequest}/approve', [CancellationController::class, 'approve'])->name('cancellations.approve');
    Route::put('cancellations/{cancellationRequest}/reject', [CancellationController::class, 'reject'])->name('cancellations.reject');

    // Category Registry
    Route::apiResource('event-categories', EventCategoryController::class)
        ->except(['show']);

    // System Settings (Profile & System Configurations)
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('settings/system', [SettingsController::class, 'updateSystem'])->name('settings.system');

    // Manual Reconciliations
    Route::post('orders/{order}/sync', [OrderController::class, 'sync'])->name('orders.sync');
});

// Organizer Routes
Route::middleware(['auth', 'verified', 'role:organizer'])->prefix('organizer')->name('organizer.')->group(function () {
    Route::get('dashboard', [OrganizerDashboardController::class, 'index'])->name('dashboard');

    Route::get('settings', [OrganizerSettingsController::class, 'index'])->name('settings');
    Route::put('settings/profile', [OrganizerSettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::put('settings/password', [OrganizerSettingsController::class, 'updatePassword'])->name('settings.password');

    // Routes requiring Admin Approval
    Route::middleware(['organizer.approved'])->group(function () {
        // Events CRUD
        Route::resource('events', App\Http\Controllers\Organizer\EventController::class);
        Route::post('events/{event}/cancel', [App\Http\Controllers\Organizer\EventController::class, 'cancel'])->name('events.cancel');
        Route::post('events/{event}/request-cancellation', [App\Http\Controllers\Organizer\EventController::class, 'requestCancellation'])->name('events.request-cancellation');

        // QR Scanner
        Route::get('scanner', [ScannerController::class, 'index'])->name('scanner.index');
        Route::post('scanner/select', [ScannerController::class, 'selectEvent'])->name('scanner.select');
        Route::post('scanner/validate', [ScannerController::class, 'validateScan'])->name('scanner.validate')->middleware('throttle:60,1');

        // Payouts & Advance Requests
        Route::get('payouts', [App\Http\Controllers\Organizer\PayoutController::class, 'index'])->name('payouts.index');
        Route::get('payouts/{event}', [App\Http\Controllers\Organizer\PayoutController::class, 'show'])->name('payouts.show');
        Route::post('payouts/{event}/request', [App\Http\Controllers\Organizer\PayoutController::class, 'requestPayout'])->name('payouts.request');
    });
});

// User Authenticated Routes
Route::middleware(['auth', 'verified', 'role:user'])->group(function () {
    // Checkout & Orders
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:5,1')->name('checkout.store');

    Route::get('/pesanan', [PesananController::class, 'index'])->name('pesanan.index');
    Route::get('/pesanan/{id}', [PesananController::class, 'show'])->name('pesanan.show');
    Route::get('/pesanan/{id}/invoice', [PesananController::class, 'invoice'])->name('pesanan.invoice');
    Route::put('/pesanan/{order}/retry', [PesananController::class, 'retryPayment'])->name('pesanan.retry');
});

// Generic Authenticated Routes (Profiles for All Roles)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
        Route::get('/verify-email/{id}/{hash}', [ProfileController::class, 'verifyPendingEmail'])
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verify-pending-email');
    });
});

Route::post('/api/payment/callback', [PaymentWebhookController::class, 'handleCallback'])->name('payment.callback');

require __DIR__.'/auth.php';
