<?php

use App\Http\Middleware\EnsurePrototypeEnabled;
use App\Prototype\PrototypeManager;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (PrototypeManager::isEnabled()) {
        return redirect()->route('prototype.index');
    }

    return view('welcome');
});

Route::prefix('prototype')
    ->name('prototype.')
    ->middleware([EnsurePrototypeEnabled::class])
    ->group(function () {
        Route::get('/', \App\Livewire\Prototype\Shell::class)->name('index');
    });
