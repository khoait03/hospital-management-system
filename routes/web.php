<?php

use Illuminate\Support\Facades\Route;

Route::get('/themeforest.net', function() {
    return view('vendor.license.index');
})->name('license');