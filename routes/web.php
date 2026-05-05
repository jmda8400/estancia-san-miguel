<?php

use App\Http\Middleware\RequireLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::view('/login', 'login')->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'username' => ['required', 'string'],
        'password' => ['required', 'string'],
    ]);

    if (
        $credentials['username'] !== env('ADMIN_USERNAME', 'admin') ||
        $credentials['password'] !== env('ADMIN_PASSWORD', 'admin')
    ) {
        return back()->withErrors([
            'username' => 'Credenciales incorrectas.',
        ]);
    }

    $request->session()->put('logged_in', true);

    return redirect()->route('stock');
})->name('login.submit');

Route::post('/logout', function (Request $request) {
    $request->session()->forget('logged_in');

    return redirect()->route('home');
})->name('logout');

Route::middleware(RequireLogin::class)->group(function () {
    Route::get('/stock', function () {
        $stockItems = DB::table('stock')->orderBy('id')->get();

        return view('stock', ['stockItems' => $stockItems]);
    })->name('stock');

    Route::view('/comandas', 'comandas')->name('comandas');
    Route::view('/admin', 'admin')->name('admin');
});