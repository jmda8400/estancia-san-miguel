<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home');

$requireLogin = function (Request $request, \Closure $next) {
    if (! $request->session()->get('logged_in')) {
        return redirect('/login');
    }

    return $next($request);
};

Route::view('/stock', 'stock')->middleware($requireLogin);
Route::view('/comandas', 'comandas')->middleware($requireLogin);
Route::view('/admin', 'admin')->middleware($requireLogin);

Route::view('/login', 'login')->name('login');

Route::post('/login', function (Request $request) {
    $request->session()->put('logged_in', true);

    return redirect('/stock');
});
