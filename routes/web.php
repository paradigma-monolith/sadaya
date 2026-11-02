<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AircraftInspectionController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FlightMedicalInspectionController;
use App\Http\Controllers\Inertia\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShipInspectionController;
use App\Http\Controllers\TravelConsentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Hybrid Architecture Web Routes
|--------------------------------------------------------------------------
| Enterprise Master Boilerplate Routes:
| 1. Guest Authentication & Password Reset Routes
| 2. Logout Route (GET & POST)
| 3. Protected Dashboard, Profile, Audit Log, Export, and SPA Routes
*/

// --- Guest Authentication Routes ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Password Reset
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');
});

// --- Public Travel Consent & Sanitation Inspection Routes ---
Route::prefix('travelconsent')->name('travelconsent.')->group(function () {
    // Public Landing Page / Hub Pelayanan
    Route::get('/home', [TravelConsentController::class, 'publicHome'])->name('home');
    Route::get('/', [TravelConsentController::class, 'publicHome'])->name('index');

    // Aircraft Sanitation
    Route::get('/public_aircraft', [TravelConsentController::class, 'publicAircraft'])->name('public_aircraft');
    Route::get('/aircraft', [TravelConsentController::class, 'publicAircraft'])->name('aircraft');
    Route::post('/submit_aircraft', [TravelConsentController::class, 'submitAircraft'])->name('submit_aircraft');
    Route::get('/receipt/{registration_no}', [TravelConsentController::class, 'receipt'])->name('receipt');
    Route::get('/pdf/{registration_no}', [TravelConsentController::class, 'downloadPdf'])->name('public_pdf');

    // Ship Sanitation (SSCC / SSCEC - Sertifikat Pengendalian Sanitasi Kapal)
    Route::get('/public_sanitasi', [TravelConsentController::class, 'publicSanitasi'])->name('public_sanitasi');
    Route::post('/submit_sanitasi', [TravelConsentController::class, 'submitSanitasi'])->name('submit_sanitasi');
    Route::get('/receipt_sanitasi/{registration_no}', [TravelConsentController::class, 'receiptSanitasi'])->name('receipt_sanitasi');
    Route::get('/doc_sanitasi/{id}', [TravelConsentController::class, 'docSanitasi'])->name('doc_sanitasi');

    // Pemakaian Alat Kesehatan Kotak P3K & Emergensi Pesawat
    Route::get('/public_alat', [TravelConsentController::class, 'publicAlat'])->name('public_alat');
    Route::post('/submit_alat', [TravelConsentController::class, 'submitAlat'])->name('submit_alat');
    Route::get('/receipt_alat/{registration_no}', [TravelConsentController::class, 'receiptAlat'])->name('receipt_alat');

    // Pemakaian Obat-obatan Kotak P3K & Emergensi Pesawat
    Route::get('/public_obat', [TravelConsentController::class, 'publicObat'])->name('public_obat');
    Route::post('/submit_obat', [TravelConsentController::class, 'submitObat'])->name('submit_obat');
    Route::get('/receipt_obat/{registration_no}', [TravelConsentController::class, 'receiptObat'])->name('receipt_obat');
});

// --- Logout Route (GET & POST) ---
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// --- Protected Enterprise Routes (Requires Authentication) ---
Route::middleware('auth')->group(function () {
    // Primary Dashboard (Landing post-login)
    Route::get('/', [DashboardController::class, 'dashboard'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

    // Inertia.js Bridge Routes
    Route::prefix('inertia')->name('inertia.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    });

    // Profile & Account Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');

    // System Activity & Audit Trail
    Route::get('/audit-logs', [ActivityLogController::class, 'index'])->name('audit.index');

    // Aircraft Sanitation Inspection Management
    Route::prefix('aircraft-inspections')->name('aircraft-inspections.')->group(function () {
        Route::get('/', [AircraftInspectionController::class, 'index'])->name('index');
        Route::redirect('/dashboard', '/dashboard');
        Route::put('/{id}/status', [AircraftInspectionController::class, 'updateStatus'])->name('updateStatus');
        Route::delete('/{id}', [AircraftInspectionController::class, 'destroy'])->name('destroy');
        Route::get('/export', [AircraftInspectionController::class, 'export'])->name('export');
        Route::get('/{id}/pdf', [AircraftInspectionController::class, 'downloadPdf'])->name('pdf');
    });

    // Flight Medical Kit Inspection Management (Alkes & Obat P3K / Emergensi)
    Route::prefix('flight-medical-inspections')->name('flight-medical-inspections.')->group(function () {
        Route::get('/alat', [FlightMedicalInspectionController::class, 'indexAlat'])->name('alat.index');
        Route::get('/obat', [FlightMedicalInspectionController::class, 'indexObat'])->name('obat.index');
        Route::put('/{id}/status', [FlightMedicalInspectionController::class, 'updateStatus'])->name('updateStatus');
        Route::delete('/{id}', [FlightMedicalInspectionController::class, 'destroy'])->name('destroy');
        Route::get('/export', [FlightMedicalInspectionController::class, 'export'])->name('export');
    });

    // Ship Sanitation Inspection Management (Karantina Kesehatan Laut - SSCC / SSCEC)
    Route::prefix('ship-inspections')->name('ship-inspections.')->group(function () {
        Route::get('/', [ShipInspectionController::class, 'index'])->name('index');
        Route::put('/{id}/status', [ShipInspectionController::class, 'updateStatus'])->name('updateStatus');
        Route::delete('/{id}', [ShipInspectionController::class, 'destroy'])->name('destroy');
        Route::get('/export', [ShipInspectionController::class, 'export'])->name('export');
        Route::get('/{id}/pdf', [ShipInspectionController::class, 'downloadPdf'])->name('pdf');
    });

    // System Data Exports (Excel, CSV, PDF)
    Route::prefix('exports/audit-logs')->name('exports.audit.')->group(function () {
        Route::get('/excel', [ExportController::class, 'exportExcel'])->name('excel');
        Route::get('/csv', [ExportController::class, 'exportCsv'])->name('csv');
        Route::get('/pdf', [ExportController::class, 'exportPdf'])->name('pdf');
    });

    // In-App & Real-Time Notifications (Reverb WebSocket Powered)
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('readAll');
    });

    // Real-Time Live Chat (Reverb WebSocket Powered)
    Route::get('/chat/messages', [\App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/messages', [\App\Http\Controllers\ChatController::class, 'send'])->name('chat.send');

    // User Impersonation Routes (/impersonate/take/{id} & /impersonate/leave)
    Route::impersonate();
});
