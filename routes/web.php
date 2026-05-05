<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::view('/', 'home');

$requireLogin = function (Request $request, \Closure $next) {
    if (! $request->session()->get('logged_in')) {
        return redirect('/login');
    }

    return $next($request);
};

Route::get('/stock', function () {
    $stockItems = DB::table('stock')->orderBy('id')->get();

    return view('stock', ['stockItems' => $stockItems]);
})->middleware($requireLogin);

Route::view('/comandas', 'comandas')->middleware($requireLogin);
Route::view('/admin', 'admin')->middleware($requireLogin);

Route::view('/login', 'login')->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'username' => ['required', 'string'],
        'password' => ['required', 'string'],
    ]);

    $user = DB::table('users')->where('name', $credentials['username'])->first();

    if (! $user || ! Hash::check($credentials['password'], $user->password)) {
        return back()->withErrors([
            'username' => 'Usuario o contraseña inválidos.',
        ])->onlyInput('username');
    }

    $request->session()->put('logged_in', true);

    return redirect('/stock');
});
