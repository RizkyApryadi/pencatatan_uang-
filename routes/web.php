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
    // sanitize input to avoid directory traversal
    $filename = str_replace(['..', "\\"], '', $filename);

    // The saved DB value may already contain the `transactions/` prefix (e.g. "transactions/abc.jpg").
    // Use the filename as-is relative to storage/app/public so we don't duplicate the segment.
    $path = storage_path('app/public/' . ltrim($filename, '/'));

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
})->name('bukti.show');

require __DIR__ . '/auth.php';
