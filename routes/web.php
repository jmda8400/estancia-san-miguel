<?php

use App\Http\Middleware\RequireLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

function comandasConProductos()
{
    return DB::table('comandas')->orderBy('mesa_numero')->get()->map(function ($c) {
        $productos = DB::table('productos as p')
            ->leftJoin('stock as s', 's.producto', '=', 'p.nombre')
            ->where('p.comanda_id', $c->id)
            ->where(function ($q) {
                $q->whereNull('s.id')->orWhere('s.cantidad', '>', 0);
            })
            ->orderBy('p.id')
            ->select('p.*')
            ->get();

        return (array) $c + ['productos' => $productos];
    });
}

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
    Route::get('/stock', fn () => view('stock', ['stockItems' => DB::table('stock')->orderBy('id')->get(), 'comandas' => DB::table('comandas')->orderBy('mesa_numero')->get()]))->name('stock');
    Route::get('/stock/data', fn () => DB::table('stock')->where('cantidad', '>', 0)->orderBy('producto')->get());
    Route::put('/stock/{id}', function (Request $r, int $id) {
        $data = $r->validate(['cantidad' => 'required|integer|min:0']);
        DB::table('stock')->where('id', $id)->update(['cantidad' => $data['cantidad'], 'updated_at' => now()]);
        return response()->noContent();
    });

    Route::get('/comandas', fn () => view('comandas', ['comandas' => comandasConProductos()]))->name('comandas');
    Route::get('/comandas/data', fn () => comandasConProductos());

    Route::post('/productos', function (Request $r) {
        $data = $r->validate([
            'comanda_id' => 'required|exists:comandas,id',
            'stock_id' => 'required|exists:stock,id',
            'cantidad' => 'required|integer|min:1',
            'notas' => 'nullable|string|max:255',
        ]);

        $stockItem = DB::table('stock')->where('id', $data['stock_id'])->first();
        if (!$stockItem || $stockItem->cantidad < $data['cantidad']) {
            return response()->json(['message' => 'Stock insuficiente para este producto.'], 422);
        }

        DB::table('productos')->insert([
            'comanda_id' => $data['comanda_id'],
            'nombre' => $stockItem->producto,
            'cantidad' => $data['cantidad'],
            'notas' => $data['notas'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('stock')->where('id', $stockItem->id)->update([
            'cantidad' => $stockItem->cantidad - $data['cantidad'],
            'updated_at' => now(),
        ]);

        return response()->noContent();
    });
    Route::put('/productos/{id}', function (Request $r, int $id) { $data=$r->validate(['cantidad'=>'nullable|integer|min:1','notas'=>'nullable|string|max:255']); DB::table('productos')->where('id',$id)->update(array_filter($data, fn($v)=>$v!==null)+['updated_at'=>now()]); return response()->noContent(); });
    Route::delete('/productos/{id}', fn (int $id) => tap(response()->noContent(), fn()=>DB::table('productos')->where('id',$id)->delete()));
    Route::post('/comandas/{id}/cobrar', function (int $id) {
        $comanda = DB::table('comandas')->where('id', $id)->first();
        abort_unless($comanda, 404);

        DB::table('productos')->where('comanda_id', $id)->delete();
        DB::table('comandas')->where('id', $id)->update([
            'nombre' => 'Mesa ' . $comanda->mesa_numero,
            'estado' => 'abierta',
            'updated_at' => now(),
        ]);

        return response()->noContent();
    });
    Route::view('/admin', 'admin')->name('admin');
});
