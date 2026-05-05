<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home');

Route::middleware(function (Request $request, \Closure $next) {
    if (! $request->session()->get('logged_in')) {
        return redirect('/login');
    }

    return $next($request);
})->group(function () {
    Route::view('/stock', 'stock');
    Route::view('/comandas', 'comandas');
    Route::view('/admin', 'admin');
});

Route::view('/login', 'login')->name('login');

Route::post('/login', function (Request $request) {
    $request->session()->put('logged_in', true);

    return redirect('/stock');
});
