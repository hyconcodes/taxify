<?php

use App\Models\PlateAlert;
use App\Models\PlateCapture;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    try {
        $stats = [
            'vehicles' => Vehicle::count(),
            'captures' => PlateCapture::count(),
            'alerts' => PlateAlert::where('status', 'alert')->count(),
        ];
    } catch (Throwable) {
        $stats = ['vehicles' => 0, 'captures' => 0, 'alerts' => 0];
    }

    return view('welcome', compact('stats'));
})->name('home');

Route::get('/register', function () {
    return view('pages::auth.register-disabled');
})->name('register');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('vehicles', 'pages::vehicles.index')->name('vehicles.index');
    Route::livewire('vehicles/create', 'pages::vehicles.create')->name('vehicles.create');

    Route::livewire('captures', 'pages::captures.index')->name('captures.index');

    Route::livewire('alerts', 'pages::alerts.index')->name('alerts.index');

    Route::livewire('plate-lookup', 'pages::plate-lookup')->name('plate-lookup');
});

require __DIR__.'/settings.php';
