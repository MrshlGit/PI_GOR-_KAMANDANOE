<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('login_view');
})->name('login');

Route::get('/register', function () {
    return view('register_view');
})->name('register');
