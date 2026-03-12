<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AlumniController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\PddiktiController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

// ─── PUBLIC: Search Mahasiswa (landing page, tanpa login) ─────────────────────
Route::get('/',     [SearchController::class, 'index'])->name('search');
Route::get('/cari', [SearchController::class, 'index'])->name('search.cari');
Route::get('/api/pddikti/detail/{id}', [SearchController::class, 'pddiktiDetailAjax'])->name('api.pddikti.detail');

// TEMPORARY DEBUG ROUTe
Route::get('/debug-pddikti', function() {
    $headers = [
        'Accept'          => 'application/json, text/plain, */*',
        'Accept-Language' => 'en-US,en;q=0.9',
        'Origin'          => 'https://pddikti.kemdiktisaintek.go.id',
        'Referer'         => 'https://pddikti.kemdiktisaintek.go.id/',
        'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/131.0.0.0 Safari/537.36',
    ];
    $res = Illuminate\Support\Facades\Http::withHeaders($headers)->withOptions(['verify' => false])->get('https://api-pddikti.kemdiktisaintek.go.id/pencarian/mhs/angga');
    return $res->json()['mahasiswa'] ?? ['error' => 'No data'];
});
Route::get('/alumni/{alumni}', [AlumniController::class, 'show'])->name('alumni.show');

// ─── AUTH ─────────────────────────────────────────────────────────────────────
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ─── ADMIN ONLY (auth + role=admin) ──────────────────────────────────────────
Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // AJAX endpoints untuk Live PDDIKTI di dashboard
    Route::get('/dashboard/pddikti-status/{id}', [DashboardController::class, 'pddiktiStatus'])->name('dashboard.pddikti.status');
    Route::get('/dashboard/pddikti-search/{keyword}', [DashboardController::class, 'pddiktiSearch'])->name('dashboard.pddikti.search');

    // Alumni CRUD (tracking lokal)
    Route::get('/alumni', [AlumniController::class, 'index'])->name('alumni.index');
    Route::get('/alumni/create', [AlumniController::class, 'create'])->name('alumni.create');
    Route::post('/alumni', [AlumniController::class, 'store'])->name('alumni.store');
    Route::get('/alumni/{alumni}/edit', [AlumniController::class, 'edit'])->name('alumni.edit');
    Route::put('/alumni/{alumni}', [AlumniController::class, 'update'])->name('alumni.update');
    Route::delete('/alumni/{alumni}', [AlumniController::class, 'destroy'])->name('alumni.destroy');

    // Tracking
    Route::post('/alumni/{alumni}/track',  [TrackingController::class, 'runSingle'])->name('tracking.single');
    Route::post('/tracking/batch',         [TrackingController::class, 'runBatch'])->name('tracking.batch');
    Route::post('/alumni/{alumni}/verify', [TrackingController::class, 'verify'])->name('tracking.verify');

    // PDDIKTI Admin Tools
    Route::get('/pddikti',            [PddiktiController::class, 'index'])->name('pddikti.search');
    Route::get('/pddikti/{id}',       [PddiktiController::class, 'detail'])->name('pddikti.detail');
    Route::post('/pddikti/{id}/save', [PddiktiController::class, 'save'])->name('pddikti.save');
});
