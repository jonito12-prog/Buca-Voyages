<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Client\AffiliateController;
use App\Http\Controllers\Client\PortalController;
use App\Http\Controllers\Backoffice\DashboardController;
use App\Http\Controllers\Backoffice\PackageController;
use App\Http\Controllers\Backoffice\PaymentController;
use App\Http\Controllers\Backoffice\TravelAffiliateController;
use App\Http\Controllers\Backoffice\TripConsumptionController;
use App\Http\Controllers\Backoffice\VipCardController;
use App\Http\Controllers\Backoffice\VipClientController;
use App\Http\Controllers\Backoffice\ParcelController;
use App\Http\Controllers\Backoffice\SecurityController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'showLogin'])->name('login');
    Route::get('/login', [LoginController::class, 'showLogin']);
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
    Route::get('/inscription', [RegisterController::class, 'showRegister'])->name('register');
    Route::post('/inscription', [RegisterController::class, 'register'])->name('register.store');
    Route::get('/mot-de-passe-oublie', [LoginController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/mot-de-passe-oublie', [LoginController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reinitialiser-mot-de-passe/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reinitialiser-mot-de-passe', [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('dashboard')->with('status', 'Adresse email verifiee avec succes.');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'Un nouveau lien de verification a ete envoye.');
    })->middleware(['throttle:6,1'])->name('verification.send');

    Route::get('/dashboard', DashboardController::class)->middleware('client.verified')->name('dashboard');
    Route::get('/mon-espace', PortalController::class)->middleware(['client.verified', 'role:client'])->name('client.portal');
    Route::post('/mon-espace/affilies', [AffiliateController::class, 'store'])->middleware(['client.verified', 'role:client'])->name('client.affiliates.store');
    Route::get('/mon-espace/forfait', [\App\Http\Controllers\Client\SubscriptionController::class, 'subscriptionForm'])->middleware(['client.verified', 'role:client'])->name('client.subscription.form');
    Route::post('/mon-espace/forfait', [\App\Http\Controllers\Client\SubscriptionController::class, 'subscribe'])->middleware(['client.verified', 'role:client'])->name('client.subscription.store');
    Route::post('/mon-espace/modifier-mot-de-passe', [\App\Http\Controllers\Client\PasswordController::class, 'update'])->middleware(['client.verified', 'role:client'])->name('client.change-password');

    Route::middleware('role:admin,agent')->group(function () {
        // Clients VIP
        Route::middleware('permission:vip_clients.manage')->group(function () {
            Route::get('/clients-vip', [VipClientController::class, 'index'])->name('vip-clients.index');
            Route::get('/clients-vip/nouveau', [VipClientController::class, 'create'])->name('vip-clients.create');
            Route::post('/clients-vip', [VipClientController::class, 'store'])->name('vip-clients.store');
            Route::get('/clients-vip/{vipClient}', [VipClientController::class, 'show'])->name('vip-clients.show');
            Route::get('/clients-vip/{vipClient}/modifier', [VipClientController::class, 'edit'])->name('vip-clients.edit');
            Route::put('/clients-vip/{vipClient}', [VipClientController::class, 'update'])->name('vip-clients.update');
            Route::patch('/clients-vip/{vipClient}/archiver', [VipClientController::class, 'archive'])->name('vip-clients.archive');
            Route::post('/clients-vip/{vipClient}/affilies', [TravelAffiliateController::class, 'store'])->name('vip-clients.affiliates.store');
            Route::patch('/clients-vip/{vipClient}/affilies/{affiliate}', [TravelAffiliateController::class, 'toggleStatus'])->name('vip-clients.affiliates.toggle');
            Route::delete('/clients-vip/{vipClient}', [VipClientController::class, 'destroy'])->name('vip-clients.destroy');
        });

        // Cartes VIP
        Route::middleware('permission:vip_cards.manage')->group(function () {
            Route::get('/clients-vip/{vipClient}/cartes/nouvelle', [VipCardController::class, 'create'])->name('vip-cards.create');
            Route::post('/clients-vip/{vipClient}/cartes', [VipCardController::class, 'store'])->name('vip-cards.store');
            Route::get('/cartes-vip/{vipCard}', [VipCardController::class, 'show'])->name('vip-cards.show');
            Route::get('/cartes-vip/{vipCard}/forfait', [VipCardController::class, 'subscriptionForm'])->name('vip-cards.subscription');
            Route::post('/cartes-vip/{vipCard}/forfait', [VipCardController::class, 'subscribe'])->name('vip-cards.subscribe');
        });

        Route::middleware('permission:vip_cards.suspend')->group(function () {
            Route::patch('/cartes-vip/{vipCard}/suspendre', [VipCardController::class, 'suspend'])->name('vip-cards.suspend');
            Route::patch('/cartes-vip/{vipCard}/reactiver', [VipCardController::class, 'reactivate'])->name('vip-cards.reactivate');
        });

        // Voyages
        Route::middleware('permission:trips.consume')->group(function () {
            Route::get('/voyages', [TripConsumptionController::class, 'index'])->name('trips.index');
            Route::get('/voyages/nouveau', [TripConsumptionController::class, 'create'])->name('trips.create');
            Route::get('/cartes-vip/{vipCard}/voyage', [TripConsumptionController::class, 'create'])->name('trips.create-for-card');
            Route::post('/voyages', [TripConsumptionController::class, 'store'])->name('trips.store');
        });

        // Paiements
        Route::middleware('permission:payments.view')->group(function () {
            Route::get('/paiements', [PaymentController::class, 'index'])->name('payments.index');
            Route::get('/paiements/{payment}', [PaymentController::class, 'show'])->name('payments.show');
        });
        
        Route::middleware('permission:payments.manage')->group(function () {
            Route::patch('/paiements/{payment}/confirmer', [PaymentController::class, 'confirm'])->name('payments.confirm');
        });

        // Gestion des Colis (Messagerie)
        Route::middleware('permission:parcels.manage')->group(function () {
            Route::get('/colis/nouveau', [ParcelController::class, 'create'])->name('parcels.create');
            Route::post('/colis', [ParcelController::class, 'store'])->name('parcels.store');
            Route::patch('/colis/{parcel}/livrer', [ParcelController::class, 'deliver'])->name('parcels.deliver');
        });

        Route::middleware('permission:parcels.manage,parcels.view')->group(function () {
            Route::get('/colis', [ParcelController::class, 'index'])->name('parcels.index');
            Route::get('/colis/{parcel}', [ParcelController::class, 'show'])->name('parcels.show');
        });

        // Gestion des Forfaits
        Route::middleware('permission:packages.manage')->group(function () {
            Route::get('/forfaits', [PackageController::class, 'index'])->name('packages.index');
            Route::get('/forfaits/nouveau', [PackageController::class, 'create'])->name('packages.create');
            Route::post('/forfaits', [PackageController::class, 'store'])->name('packages.store');
            Route::get('/forfaits/{package}/modifier', [PackageController::class, 'edit'])->name('packages.edit');
            Route::put('/forfaits/{package}', [PackageController::class, 'update'])->name('packages.update');
            Route::patch('/forfaits/{package}/statut', [PackageController::class, 'toggleStatus'])->name('packages.toggle-status');
        });

        // Gestion de la Sécurité (RBAC)
        Route::middleware('permission:roles.manage,users.manage')->group(function () {
            Route::get('/securite', [SecurityController::class, 'index'])->name('security.index');
            Route::post('/securite/utilisateurs/{user}/role', [SecurityController::class, 'updateUserRole'])->name('security.users.update-role');
        });

        Route::middleware('permission:roles.manage')->group(function () {
            Route::get('/securite/roles/nouveau', [SecurityController::class, 'createRole'])->name('security.roles.create');
            Route::post('/securite/roles', [SecurityController::class, 'storeRole'])->name('security.roles.store');
            Route::get('/securite/roles/{role}/modifier', [SecurityController::class, 'editRole'])->name('security.roles.edit');
            Route::put('/securite/roles/{role}', [SecurityController::class, 'updateRole'])->name('security.roles.update');
            Route::delete('/securite/roles/{role}', [SecurityController::class, 'destroyRole'])->name('security.roles.destroy');
        });
    });
});


