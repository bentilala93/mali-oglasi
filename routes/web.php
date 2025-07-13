<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Volt::route('/', 'home')->name('home');
Volt::route('/oglasi/{categorySlug?}', 'home')->name('oglasi.category');
Volt::route('/oglasi/{categories}/{adSlug}', 'single-ad')->name('oglas.show.dynamic')->where('categories', '.*');

Route::middleware(['auth'])->group(function () {
    Volt::route('/dashboard', 'dashboard-redirect')->name('dashboard');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Volt::route('/admin/oglasi', 'admin-oglasi')->name('admin.oglasi');
    Volt::route('/admin/kategorije', 'admin-kategorije')->name('admin.kategorije');
    Volt::route('/admin/korisnici', 'admin-korisnici')->name('admin.korisnici');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
