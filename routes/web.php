<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AircraftInspectionController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DataMigrationController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FlightMedicalInspectionController;
use App\Http\Controllers\Inertia\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PrlController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShipHealthSurveillanceController;
use App\Http\Controllers\ShipInspectionController;
use App\Http\Controllers\SinkarkesController;
use App\Http\Controllers\SinkarkesDailyController;
use App\Http\Controllers\SiveraController;
use App\Http\Controllers\TravelConsentController;
use App\Http\Controllers\TravelConsentHubController;
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
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

    // Password Reset
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
        ->middleware('throttle:password-reset')
        ->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])
        ->middleware('throttle:password-reset')
        ->name('password.update');
});

// --- Convenient Top-Level Shortcut & Alias Routes (Redirect to travelconsent & ship-surveillance) ---
Route::redirect('/public_aircraft', '/travelconsent/public_aircraft');
Route::redirect('/public_sanitasi', '/travelconsent/public_sanitasi');
Route::redirect('/public_alat', '/travelconsent/public_alat');
Route::redirect('/public_obat', '/travelconsent/public_obat');
Route::redirect('/aircraft', '/travelconsent/public_aircraft');
Route::redirect('/sanitasi', '/travelconsent/public_sanitasi');
Route::redirect('/kapal', '/travelconsent/public_sanitasi');
Route::redirect('/alkes', '/travelconsent/public_alat');
Route::redirect('/obat', '/travelconsent/public_obat');
Route::redirect('/quarantine', '/ship-surveillance/assessment/domestic');
Route::redirect('/quarantine/english', '/ship-surveillance/assessment/international');
Route::redirect('/selfassessment', '/ship-surveillance/portal');
Route::redirect('/tangkap', '/ship-surveillance/portal');

// --- Public Ship Health Quarantine Assessment Routes (TANGKAP - Native Sadaya) ---
Route::prefix('ship-surveillance')->name('ship-surveillance.')->group(function () {
    Route::get('/portal', [ShipHealthSurveillanceController::class, 'publicPortal'])->name('portal');
    Route::get('/assessment/domestic', [ShipHealthSurveillanceController::class, 'publicDomestic'])->name('assessment.domestic');
    Route::get('/assessment/international', [ShipHealthSurveillanceController::class, 'publicInternational'])->name('assessment.international');
    Route::post('/assessment/submit', [ShipHealthSurveillanceController::class, 'submitAssessment'])
        ->middleware('throttle:public-submit')
        ->name('assessment.submit');
    Route::get('/receipt/{registration_no}', [ShipHealthSurveillanceController::class, 'receipt'])->name('receipt');
});

// --- Public Travel Consent & Sanitation Inspection Routes ---
Route::prefix('travelconsent')->name('travelconsent.')->group(function () {
    // Public Landing Page / Hub Pelayanan
    Route::get('/home', [TravelConsentController::class, 'publicHome'])->name('home');
    Route::get('/', [TravelConsentController::class, 'publicHome'])->name('index');

    // Aircraft Sanitation
    Route::get('/public_aircraft', [TravelConsentController::class, 'publicAircraft'])->name('public_aircraft');
    Route::get('/aircraft', [TravelConsentController::class, 'publicAircraft'])->name('aircraft');
    Route::post('/submit_aircraft', [TravelConsentController::class, 'submitAircraft'])
        ->middleware('throttle:public-submit')
        ->name('submit_aircraft');
    Route::get('/receipt/{registration_no}', [TravelConsentController::class, 'receipt'])->name('receipt');
    Route::get('/pdf/{registration_no}', [TravelConsentController::class, 'downloadPdf'])->name('public_pdf');

    // Ship Sanitation (SSCC / SSCEC - Sertifikat Pengendalian Sanitasi Kapal)
    Route::get('/public_sanitasi', [TravelConsentController::class, 'publicSanitasi'])->name('public_sanitasi');
    Route::post('/submit_sanitasi', [TravelConsentController::class, 'submitSanitasi'])
        ->middleware('throttle:public-submit')
        ->name('submit_sanitasi');
    Route::get('/receipt_sanitasi/{registration_no}', [TravelConsentController::class, 'receiptSanitasi'])->name('receipt_sanitasi');
    Route::get('/doc_sanitasi/{id}', [TravelConsentController::class, 'docSanitasi'])->name('doc_sanitasi');

    // Pemakaian Alat Kesehatan Kotak P3K & Emergensi Pesawat
    Route::get('/public_alat', [TravelConsentController::class, 'publicAlat'])->name('public_alat');
    Route::post('/submit_alat', [TravelConsentController::class, 'submitAlat'])
        ->middleware('throttle:public-submit')
        ->name('submit_alat');
    Route::get('/receipt_alat/{registration_no}', [TravelConsentController::class, 'receiptAlat'])->name('receipt_alat');

    // Pemakaian Obat-obatan Kotak P3K & Emergensi Pesawat
    Route::get('/public_obat', [TravelConsentController::class, 'publicObat'])->name('public_obat');
    Route::post('/submit_obat', [TravelConsentController::class, 'submitObat'])
        ->middleware('throttle:public-submit')
        ->name('submit_obat');
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

    // System Activity & Audit Trail (Restricted to Admin / Super Admin)
    Route::get('/audit-logs', [ActivityLogController::class, 'index'])
        ->middleware('role:Super Admin|Admin')
        ->name('audit.index');

    // Unified Travel Consent Multi-Tab Hub
    Route::prefix('travel-consent')->name('travel-consent.')->group(function () {
        Route::get('/', [TravelConsentHubController::class, 'index'])->name('index');
        Route::redirect('/portal', '/travel-consent')->name('portal');
        Route::get('/aircraft', [TravelConsentHubController::class, 'aircraftTab'])->name('aircraft');
        Route::get('/ship', [TravelConsentHubController::class, 'shipTab'])->name('ship');
        Route::get('/alat', [TravelConsentHubController::class, 'alatTab'])->name('alat');
        Route::get('/obat', [TravelConsentHubController::class, 'obatTab'])->name('obat');
    });

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

    // Pengawasan Kekarantinaan Kedatangan Kapal (Maritime Declaration of Health / MDH)
    Route::prefix('ship-surveillance')->name('ship-surveillance.')->group(function () {
        Route::get('/', [ShipHealthSurveillanceController::class, 'index'])->name('index');
        Route::get('/export/csv', [ShipHealthSurveillanceController::class, 'export'])->name('export');
        Route::post('/import/test-connection', [ShipHealthSurveillanceController::class, 'testImportConnection'])->name('import.test');
        Route::post('/import/direct', [ShipHealthSurveillanceController::class, 'importDirect'])->name('import.direct');
        Route::post('/import/file', [ShipHealthSurveillanceController::class, 'importFile'])->name('import.file');

        // Manajemen & Import Negara Zona Merah (PDF Kemenkes)
        Route::prefix('red-zones')->name('red-zones.')->group(function () {
            Route::get('/', [ShipHealthSurveillanceController::class, 'redZonesIndex'])->name('index');
            Route::post('/preview-pdf', [ShipHealthSurveillanceController::class, 'previewRedZonePdf'])->name('preview');
            Route::post('/apply', [ShipHealthSurveillanceController::class, 'applyRedZoneUpdate'])->name('apply');
            Route::post('/country', [ShipHealthSurveillanceController::class, 'storeRedZoneCountry'])->name('store');
            Route::patch('/{country}/toggle', [ShipHealthSurveillanceController::class, 'toggleRedZoneCountry'])->whereNumber('country')->name('toggle');
            Route::post('/reset', [ShipHealthSurveillanceController::class, 'resetRedZoneDefaults'])->name('reset');
        });

        Route::get('/{id}', [ShipHealthSurveillanceController::class, 'show'])->whereNumber('id')->name('show');
        Route::patch('/{id}/gross-tonnage', [ShipHealthSurveillanceController::class, 'updateGrossTonnage'])->whereNumber('id')->name('updateGrossTonnage');
        Route::put('/{id}/verify', [ShipHealthSurveillanceController::class, 'verify'])->whereNumber('id')->name('verify');
        Route::delete('/{id}', [ShipHealthSurveillanceController::class, 'destroy'])->whereNumber('id')->name('destroy');
    });

    // SINKARKES - Laporan Karantina Kesehatan Terpadu BKK Kelas I Bandung
    Route::prefix('sinkarkes')->name('sinkarkes.')->group(function () {
        Route::get('/dashboard', [SinkarkesController::class, 'dashboard'])->name('dashboard');
        Route::get('/', [SinkarkesController::class, 'index'])->name('index');
        Route::post('/', [SinkarkesController::class, 'store'])->name('store');
        Route::get('/export', [SinkarkesController::class, 'exportExcel'])->name('export');
        Route::post('/import', [SinkarkesController::class, 'importExcel'])->name('import');
    });

    // SINKARKES Harian - Rekap Data Harian Kapal & Pesawat (akumulasi ke SINKARKES)
    Route::prefix('sinkarkes-daily')->name('sinkarkes-daily.')->group(function () {
        Route::get('/', [SinkarkesDailyController::class, 'index'])->name('index');
        Route::post('/', [SinkarkesDailyController::class, 'store'])->name('store');
        Route::delete('/{id}', [SinkarkesDailyController::class, 'destroy'])->whereNumber('id')->name('destroy');
        Route::post('/sync', [SinkarkesDailyController::class, 'sync'])->name('sync');
        Route::post('/import', [SinkarkesDailyController::class, 'importExcel'])->name('import');
        Route::get('/export', [SinkarkesDailyController::class, 'exportExcel'])->name('export');
    });

    // Sanitasi Lingkungan (Pengawasan TPP, SAB, HSBU)
    Route::prefix('sanitasi-lingkungan')->name('sanitasi.')->group(function () {
        Route::get('/', [PrlController::class, 'sanitasiIndex'])->name('index');
        Route::get('/tpp', [PrlController::class, 'tppTab'])->name('tpp');
        Route::get('/sab', [PrlController::class, 'sabTab'])->name('sab');
        Route::get('/hsbu', [PrlController::class, 'hsbuTab'])->name('hsbu');
        Route::get('/export/{type}', [PrlController::class, 'exportExcel'])->name('export');
        Route::get('/inspeksi/{type}/{id}/pdf', [PrlController::class, 'printPdf'])->name('pdf');
        Route::post('/tpp', [PrlController::class, 'storeTpp'])->name('storeTpp');
        Route::post('/sab', [PrlController::class, 'storeSab'])->name('storeSab');
        Route::post('/hsbu', [PrlController::class, 'storeHsbu'])->name('storeHsbu');
        Route::post('/inspeksi/tpp', [PrlController::class, 'storeInspeksiTpp'])->name('storeInspeksiTpp');
        Route::post('/inspeksi/sab', [PrlController::class, 'storeInspeksiSab'])->name('storeInspeksiSab');
        Route::post('/inspeksi/hsbu', [PrlController::class, 'storeInspeksiHsbu'])->name('storeInspeksiHsbu');
        Route::delete('/inspeksi/{type}/{id}', [PrlController::class, 'destroyInspeksi'])->name('destroyInspeksi');
        Route::delete('/sarana/{type}/{id}', [PrlController::class, 'destroySarana'])->name('destroySarana');
    });

    // Surveilans Vektor dan Reservoar (SIVERA)
    Route::prefix('sivera')->name('sivera.')->group(function () {
        Route::get('/', [SiveraController::class, 'index'])->name('index');
        Route::get('/dashboard', [SiveraController::class, 'index'])->name('dashboard');
        Route::get('/vektor/{vektor}', [SiveraController::class, 'index'])->name('vektor');
        Route::get('/export', [SiveraController::class, 'exportExcel'])->name('export');
        Route::get('/{id}/pdf/{tipe?}', [SiveraController::class, 'printPdf'])->name('pdf');
        Route::get('/jentik/pdf/{id}/{tipe?}', [SiveraController::class, 'printJentikPdf'])->name('jentik.pdf');
        Route::get('/lalat/pdf/{id}', [SiveraController::class, 'printLalatPdf'])->name('lalat.pdf');
        Route::post('/', [SiveraController::class, 'store'])->name('store');
        Route::put('/{id}', [SiveraController::class, 'update'])->name('update');
        Route::post('/{id}/pengendalian', [SiveraController::class, 'updatePengendalian'])->name('pengendalian');
        Route::delete('/{id}', [SiveraController::class, 'destroy'])->name('destroy');
    });

    // Pengendalian Risiko Lingkungan (PRL / KESLING - Backwards Compatibility Aliases)
    Route::prefix('prl')->name('prl.')->group(function () {
        Route::get('/', [PrlController::class, 'index'])->name('index');
        Route::get('/tpp', [PrlController::class, 'tppTab'])->name('tpp');
        Route::get('/sab', [PrlController::class, 'sabTab'])->name('sab');
        Route::get('/hsbu', [PrlController::class, 'hsbuTab'])->name('hsbu');
        Route::get('/sivera', [PrlController::class, 'siveraTab'])->name('sivera');
        Route::get('/export/{type}', [PrlController::class, 'exportExcel'])->name('export');
        Route::get('/inspeksi/{type}/{id}/pdf', [PrlController::class, 'printPdf'])->name('pdf');
        Route::get('/sivera/{id}/pdf/{tipe?}', [PrlController::class, 'printSiveraPdf'])->name('sivera.pdf');
        Route::get('/sivera_jentik/pdf/{id}/{tipe?}', [PrlController::class, 'printSiveraJentikPdf'])->name('sivera.jentik.pdf');
        Route::get('/sivera_lalat/pdf/{id}', [PrlController::class, 'printSiveraLalatPdf'])->name('sivera.lalat.pdf');
        Route::post('/tpp', [PrlController::class, 'storeTpp'])->name('storeTpp');
        Route::post('/sab', [PrlController::class, 'storeSab'])->name('storeSab');
        Route::post('/hsbu', [PrlController::class, 'storeHsbu'])->name('storeHsbu');
        Route::post('/inspeksi/tpp', [PrlController::class, 'storeInspeksiTpp'])->name('storeInspeksiTpp');
        Route::post('/inspeksi/sab', [PrlController::class, 'storeInspeksiSab'])->name('storeInspeksiSab');
        Route::post('/inspeksi/hsbu', [PrlController::class, 'storeInspeksiHsbu'])->name('storeInspeksiHsbu');
        Route::post('/sivera', [PrlController::class, 'storeSivera'])->name('storeSivera');
        Route::put('/sivera/{id}', [PrlController::class, 'updateSivera'])->name('updateSivera');
        Route::post('/sivera/{id}/pengendalian', [PrlController::class, 'updatePengendalianSivera'])->name('updatePengendalianSivera');
        Route::delete('/inspeksi/{type}/{id}', [PrlController::class, 'destroyInspeksi'])->name('destroyInspeksi');
        Route::delete('/sarana/{type}/{id}', [PrlController::class, 'destroySarana'])->name('destroySarana');
        Route::post('/sync', [PrlController::class, 'syncFromSimply'])->name('sync');
        Route::post('/test-connection', [PrlController::class, 'testSimplyConnection'])->name('testConnection');
    });

    // System Data Exports (Excel, CSV, PDF — Admin / Super Admin)
    Route::prefix('exports/audit-logs')
        ->middleware(['role:Super Admin|Admin', 'throttle:exports'])
        ->name('exports.audit.')
        ->group(function () {
            Route::get('/excel', [ExportController::class, 'exportExcel'])->name('excel');
            Route::get('/csv', [ExportController::class, 'exportCsv'])->name('csv');
            Route::get('/pdf', [ExportController::class, 'exportPdf'])->name('pdf');
        });

    // Dokumentasi Manual Book PDF
    Route::get('/docs/manual-book/download', [ExportController::class, 'exportManualBookPdf'])->name('docs.manual-book.download');

    // Pusat Migrasi & Import Database Simply (CI4) -> Sadaya (Hanya Super Admin & Admin)
    Route::prefix('database-import')->middleware('role:Super Admin|Admin')->name('database-import.')->group(function () {
        Route::get('/', [DataMigrationController::class, 'index'])->name('index');
        Route::post('/save-config', [DataMigrationController::class, 'saveConfig'])->name('saveConfig');
        Route::post('/save-auto-sync', [DataMigrationController::class, 'saveAutoSync'])->name('saveAutoSync');
        Route::post('/sync-now', [DataMigrationController::class, 'runAutoSyncNow'])->name('syncNow');
        Route::post('/test-connection', [DataMigrationController::class, 'testConnection'])->name('test');
        Route::post('/migrate', [DataMigrationController::class, 'migrate'])->name('migrate');
        Route::post('/import-file', [DataMigrationController::class, 'importFile'])->name('importFile');
    });

    // In-App & Real-Time Notifications (Reverb WebSocket Powered)
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('readAll');
    });

    // Real-Time Live Chat (Reverb WebSocket Powered)
    Route::get('/chat/messages', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/messages', [ChatController::class, 'send'])->name('chat.send');

    // User Impersonation Routes (/impersonate/take/{id} & /impersonate/leave)
    Route::impersonate();
});
