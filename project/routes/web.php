<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;

Route::get('/', function () {
    return view('welcome');
});

Route::get('save', [Controller::class, 'save'])->name('save');

Route::get('show', [Controller::class, 'show'])->name('show');
