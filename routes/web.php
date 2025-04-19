<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ChurchController;
use App\Http\Controllers\JemaatController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\AnnouncementController;

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::resource('churches', ChurchController::class);
    Route::resource('jemaat', JemaatController::class);
    Route::resource('pengumuman', AnnouncementController::class)->parameters([
        'pengumuman' => 'announcement'
    ]);

    Route::get('/jadwal', function () {
        return view('jadwal.index');
    })->name('jadwal.index');
});
