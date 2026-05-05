<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'home');
Route::view('/stock', 'stock');
Route::view('/comandas', 'comandas');
Route::view('/admin', 'admin');
Route::view('/login', 'login');
