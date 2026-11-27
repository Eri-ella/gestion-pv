<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EtudiantController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\PvController;
use App\Http\Controllers\ReferentielController;
use App\Http\Controllers\UtilisateurController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => view('auth.login'))->name('login');
    Route::post('/login', AuthenticatedSessionController::class);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::controller(PvController::class)->prefix('pvs')->name('pvs.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::get('/import', 'importForm')->name('import');
        Route::post('/import', 'import')->name('import.store');
        Route::post('/', 'store')->name('store');
        Route::get('/{pv}', 'show')->name('show');
        Route::get('/{pv}/edit', 'edit')->name('edit');
        Route::put('/{pv}', 'update')->name('update');
        Route::post('/{pv}/valider', 'valider')->name('valider');
        Route::post('/{pv}/archiver', 'archiver')->name('archiver');
        Route::get('/{pv}/fichier', 'fichier')->name('fichier');
        Route::post('/{pv}/resultats', 'storeResultats')->name('resultats.store');
    });

    Route::controller(EtudiantController::class)->prefix('etudiants')->name('etudiants.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{etudiant}', 'show')->name('show');
    });

    Route::controller(ReferentielController::class)->prefix('referentiels')->name('referentiels.')
        ->middleware('role:administrateur')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/{type}', 'store')->name('store');
            Route::delete('/{type}/{id}', 'destroy')->name('destroy');
        });

    Route::controller(UtilisateurController::class)->prefix('utilisateurs')->name('utilisateurs.')
        ->middleware('role:administrateur')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::post('/{user}/toggle', 'toggle')->name('toggle');
        });

    Route::get('/journal', [JournalController::class, 'index'])
        ->middleware('role:administrateur')
        ->name('journal.index');
});
