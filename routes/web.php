<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/kiosk', function () {
    return view('kiosk');
})->name('kiosk');
