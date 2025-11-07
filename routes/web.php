<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;


Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');


// group auth
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});


Route::get('/bukti/{filename}', function ($filename) {
    $path = storage_path('app/public/transactions/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
})->name('bukti.show');

require __DIR__ . '/auth.php';
