<?php

use App\Http\Middleware\RequireLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::view('/login', 'login')->name('login');
Route::post('/login', function (Request $request) {
    $credentials = $request->validate(['username' => ['required', 'string'], 'password' => ['required', 'string']]);
    if ($credentials['username'] !== env('ADMIN_USERNAME', 'admin') || $credentials['password'] !== env('ADMIN_PASSWORD', 'admin')) {
        return back()->withErrors(['username' => 'Credenciales incorrectas.']);
    }
    $request->session()->put('logged_in', true);
    return redirect()->route('stock');
})->name('login.submit');
Route::post('/logout', function (Request $request) { $request->session()->forget('logged_in'); return redirect()->route('home'); })->name('logout');

Route::middleware(RequireLogin::class)->group(function () {
    Route::get('/stock', fn () => view('stock', ['stockItems' => DB::table('stock')->orderBy('id')->get()]))->name('stock');
    Route::get('/comandas', fn () => view('comandas', ['comandas' => DB::table('comandas')->orderBy('mesa_numero')->get()->map(fn($c)=> (array)$c + ['productos'=>DB::table('productos')->where('comanda_id',$c->id)->orderBy('id')->get()])]))->name('comandas');
    Route::get('/comandas/data', fn () => DB::table('comandas')->orderBy('mesa_numero')->get()->map(fn($c)=> (array)$c + ['productos'=>DB::table('productos')->where('comanda_id',$c->id)->orderBy('id')->get()]));
    Route::post('/productos', function (Request $r) { $data=$r->validate(['comanda_id'=>'required|exists:comandas,id','nombre'=>'required|string|max:255','cantidad'=>'required|integer|min:1','notas'=>'nullable|string|max:255']); DB::table('productos')->insert($data + ['created_at'=>now(),'updated_at'=>now()]); return response()->noContent(); });
    Route::put('/productos/{id}', function (Request $r, int $id) { $data=$r->validate(['cantidad'=>'nullable|integer|min:1','notas'=>'nullable|string|max:255']); DB::table('productos')->where('id',$id)->update(array_filter($data, fn($v)=>$v!==null)+['updated_at'=>now()]); return response()->noContent(); });
    Route::delete('/productos/{id}', fn (int $id) => tap(response()->noContent(), fn()=>DB::table('productos')->where('id',$id)->delete()));
    Route::view('/admin', 'admin')->name('admin');
});
