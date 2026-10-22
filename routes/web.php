<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TrialReportController;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsurePrototypeEnabled;
use App\Livewire\Prototype\Shell;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'app' : 'login'));
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});
Route::middleware(['auth', EnsureAdmin::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::view('/app', 'foundation')->name('app');
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::get('/attachments/{attachment}/preview', [AttachmentController::class, 'preview'])->name('attachments.preview');
    Route::get('/attachments/{attachment}/thumbnail', [AttachmentController::class, 'thumbnail'])->name('attachments.thumbnail');
    Route::get('/progresses/{progress}/trial-report', [TrialReportController::class, 'show'])->name('progresses.trial-report');
    Route::prefix('prototype')->name('prototype.')->middleware(EnsurePrototypeEnabled::class)->group(function () {
        Route::get('/', Shell::class)->name('index');
    });
});
