<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ChurchController;
use App\Http\Controllers\JemaatController;

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::resource('churches', ChurchController::class);
    Route::resource('jemaat', JemaatController::class);

    Route::get('/pengumuman', function () {
        return view('pengumuman.index');
    })->name('pengumuman.index');

    Route::get('/jadwal', function () {
        return view('jadwal.index');
    })->name('jadwal.index');
});
