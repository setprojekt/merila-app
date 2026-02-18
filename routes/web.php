<?php

use App\Models\DeliveryNote;
use Illuminate\Support\Facades\Route;

// Ruta za neprijavljene uporabnike (Laravel pričakuje route('login'))
Route::get('/login', fn () => redirect('/admin/login'))->name('login');

Route::get('/', function () {
    if (!auth()->check()) {
        return redirect('/admin'); // Preusmeri na glavni login (nadzorna plošča)
    }
    return redirect('/admin');
});

// Prenos certifikata (za avtorizirane uporabnike)
Route::get('/certificate/{certificate}/download', App\Http\Controllers\CertificateDownloadController::class)
    ->name('certificate.download')
    ->middleware('auth');

// Print routes
Route::get('/print/delivery-note/{deliveryNote}', function (DeliveryNote $deliveryNote) {
    return view('print.delivery-note', [
        'deliveryNote' => $deliveryNote->load('items.instrument', 'sender'),
    ]);
})->name('print.delivery-note')->middleware('auth');

// Fallback GET routes za avtomatsko odjavo (Filament pričakuje POST, JavaScript pošlje GET)
Route::get('/admin/logout', function () {
    if (auth()->check()) {
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();
    }
    return redirect('/admin');
})->name('admin.logout.fallback');

Route::get('/super-admin/logout', function () {
    if (auth()->check()) {
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();
    }
    return redirect('/super-admin');
})->name('super-admin.logout.fallback');

Route::get('/merila/logout', function () {
    if (auth()->check()) {
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();
    }
    return redirect('/merila');
})->name('merila.logout.fallback');

// Fallback POST routes za login strani (prepreči "Method Not Allowed" napako)
// Ko seja poteče med POST requestom, Laravel preusmeri na login z isto metodo
Route::post('/admin/login', function () {
    return redirect('/admin');
})->name('admin.login.post.fallback');

Route::post('/super-admin/login', function () {
    return redirect('/super-admin');
})->name('super-admin.login.post.fallback');

Route::post('/merila/login', function () {
    return redirect('/merila');
})->name('merila.login.post.fallback');
