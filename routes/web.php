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
use App\Http\Controllers\ChristmasScheduleController;
use App\Http\Controllers\YouthVisitScheduleController;
use App\Http\Controllers\KeuanganController;

// Public routes
Route::get('/', [WorshipScheduleController::class, 'index'])->name('home');
Route::get('/about', [App\Http\Controllers\AboutController::class, 'index'])->name('about');
Route::get('/history', function () {
    return view('history');
})->name('history');

// Add these public routes for the menu items we made public
Route::get('/jemaat', [JemaatController::class, 'index'])->name('jemaat.index');
Route::get('/pengumuman', [AnnouncementController::class, 'index'])->name('pengumuman.index');
Route::get('/keuangan', [KeuanganController::class, 'index'])->name('keuangan.index');

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::resource('churches', ChurchController::class);
    Route::resource('jemaat', JemaatController::class, ['except' => ['show', 'index']]);
    Route::resource('pengumuman', AnnouncementController::class, ['except' => ['index']])->parameters([
        'pengumuman' => 'announcement'
    ]);
    
    // Keuangan Routes
    Route::resource('keuangan', KeuanganController::class, ['except' => ['index']]);
    Route::get('/keuangan-download', [KeuanganController::class, 'download'])->name('keuangan.download');

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

        // Christmas schedules routes
        Route::get('/christmas', [ChristmasScheduleController::class, 'index'])->name('christmas.index');
        Route::get('/christmas/create', [ChristmasScheduleController::class, 'create'])->name('christmas.create');
        Route::post('/christmas', [ChristmasScheduleController::class, 'store'])->name('christmas.store');
        Route::get('/christmas/{schedule}/edit', [ChristmasScheduleController::class, 'edit'])->name('christmas.edit');
        Route::put('/christmas/{schedule}', [ChristmasScheduleController::class, 'update'])->name('christmas.update');
        Route::delete('/christmas/{schedule}', [ChristmasScheduleController::class, 'destroy'])->name('christmas.destroy');

        // Youth Visit Schedules routes
        Route::get('/youth-visit', [YouthVisitScheduleController::class, 'index'])->name('youth-visit.index');
        Route::get('/youth-visit/create', [YouthVisitScheduleController::class, 'create'])->name('youth-visit.create');
        Route::post('/youth-visit', [YouthVisitScheduleController::class, 'store'])->name('youth-visit.store');
        Route::get('/youth-visit/{schedule}/edit', [YouthVisitScheduleController::class, 'edit'])->name('youth-visit.edit');
        Route::put('/youth-visit/{schedule}', [YouthVisitScheduleController::class, 'update'])->name('youth-visit.update');
        Route::delete('/youth-visit/{schedule}', [YouthVisitScheduleController::class, 'destroy'])->name('youth-visit.destroy');
    });

    // Add this to your routes/web.php file
    Route::get('/jemaat/export', [App\Http\Controllers\JemaatController::class, 'export'])->name('jemaat.export');
});
