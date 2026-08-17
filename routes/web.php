<?php

use App\Http\Controllers\PlateImageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/register', function () {
    return view('pages::auth.register-disabled');
})->name('register');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('vehicles', 'pages::vehicles.index')->name('vehicles.index');
    Route::livewire('vehicles/create', 'pages::vehicles.create')->name('vehicles.create');

    Route::livewire('captures', 'pages::captures.index')->name('captures.index');

    Route::get('captures/{capture}/image', PlateImageController::class)->name('captures.image');

    Route::livewire('alerts', 'pages::alerts.index')->name('alerts.index');

    Route::livewire('plate-lookup', 'pages::plate-lookup')->name('plate-lookup');
});

require __DIR__.'/settings.php';
