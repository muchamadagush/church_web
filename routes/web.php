<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ChurchController;
use App\Http\Controllers\JemaatController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\WorshipScheduleController;
use App\Http\Controllers\PrayerScheduleController;
use App\Http\Controllers\VisitScheduleController;
use App\Http\Controllers\WomenVisitScheduleController;
use App\Http\Controllers\SermonScheduleController;

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

    Route::prefix('worship-schedules')->name('worship-schedules.')->group(function () {
        // Prayer schedules routes with nested names
        Route::resource('prayer-schedules', PrayerScheduleController::class)->names([
            'index' => 'prayer-schedules.index',
            'create' => 'prayer-schedules.create',
            'store' => 'prayer-schedules.store',
            'edit' => 'prayer-schedules.edit',
            'update' => 'prayer-schedules.update',
            'destroy' => 'prayer-schedules.destroy'
        ]);
        
        // Sermon exchange schedules routes
        Route::get('/', [WorshipScheduleController::class, 'index'])->name('index');
        Route::get('/sermons', [SermonScheduleController::class, 'index'])->name('sermons.index');
        Route::get('/sermons/create', [SermonScheduleController::class, 'create'])->name('sermons.create');
        Route::post('/sermons', [SermonScheduleController::class, 'store'])->name('sermons.store');
        Route::get('/sermons/{schedule}/edit', [SermonScheduleController::class, 'edit'])->name('sermons.edit');
        Route::put('/sermons/{schedule}', [SermonScheduleController::class, 'update'])->name('sermons.update');
        Route::delete('/sermons/{schedule}', [SermonScheduleController::class, 'destroy'])->name('sermons.destroy');

        // Villa head visit schedules routes
        Route::get('/visits', [VisitScheduleController::class, 'index'])->name('visits.index');
        Route::get('/visits/create', [VisitScheduleController::class, 'create'])->name('visits.create');
        Route::post('/visits', [VisitScheduleController::class, 'store'])->name('visits.store');
        Route::get('/visits/{schedule}/edit', [VisitScheduleController::class, 'edit'])->name('visits.edit');
        Route::put('/visits/{schedule}', [VisitScheduleController::class, 'update'])->name('visits.update');
        Route::delete('/visits/{schedule}', [VisitScheduleController::class, 'destroy'])->name('visits.destroy');

        // Women visit schedules routes
        Route::get('/women-visits', [WomenVisitScheduleController::class, 'index'])->name('women-visits.index');
        Route::get('/women-visits/create', [WomenVisitScheduleController::class, 'create'])->name('women-visits.create');
        Route::post('/women-visits', [WomenVisitScheduleController::class, 'store'])->name('women-visits.store');
        Route::get('/women-visits/{schedule}/edit', [WomenVisitScheduleController::class, 'edit'])->name('women-visits.edit');
        Route::put('/women-visits/{schedule}', [WomenVisitScheduleController::class, 'update'])->name('women-visits.update');
        Route::delete('/women-visits/{schedule}', [WomenVisitScheduleController::class, 'destroy'])->name('women-visits.destroy');
    });

});
