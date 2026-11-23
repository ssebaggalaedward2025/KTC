<?php

use Illuminate\Support\Facades\Route;

/*
| Public pages
*/
Route::view('/', 'welcome')->name('home');

/*
| Signed-in users (staff and applicants)
*/
Route::middleware('auth')->group(function () {
    // Single post-login landing point: staff → Filament panel, applicants → portal.
    Route::get('/dashboard', fn () => redirect()->to(auth()->user()->homeUrl()))->name('dashboard');

    // Reachable without 2FA so that users who must enrol can do so.
    Route::view('/account/security', 'account.security')->name('account.security');

    /*
    | Applicant portal (Livewire pages are added in feature 8)
    */
    Route::middleware(['two-factor.enrolled', 'role:applicant'])
        ->prefix('portal')->name('portal.')
        ->group(function () {
            Route::view('/', 'portal.dashboard')->name('dashboard');
        });
});
