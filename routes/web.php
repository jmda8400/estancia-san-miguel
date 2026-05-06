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

function historialComandasPaginado(int $page = 1, int $perPage = 10)
{
    $page = max(1, $page);
    $total = DB::table('comandas_historial')->count();
    $lastPage = max(1, (int) ceil($total / $perPage));
    $page = min($page, $lastPage);

    $items = DB::table('comandas_historial')
        ->orderByDesc('cobrada_en')
        ->forPage($page, $perPage)
        ->get()
        ->map(function ($h) {
            $h->productos = DB::table('productos_historial')
                ->where('comanda_historial_id', $h->id)
                ->orderBy('id')
                ->get();
            return $h;
        });

    return [
        'data' => $items,
        'current_page' => $page,
        'per_page' => $perPage,
        'total' => $total,
        'last_page' => $lastPage,
    ];
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
    Route::get('/stock', fn () => view('stock', ['stockItems' => DB::table('stock')->orderBy('id')->get()]))->name('stock');
    Route::get('/stock/data', fn () => DB::table('stock')->orderBy('id')->get());
    Route::get('/stock/historial/data', fn () => DB::table('stock_historial')->orderByDesc('created_at')->limit(100)->get());
    Route::put('/stock/{id}', function (Request $r, int $id) {
        $data = $r->validate(['cantidad' => 'required|integer|min:0']);
        $stockItem = DB::table('stock')->where('id', $id)->first();
        abort_unless($stockItem, 404);

        DB::table('stock')->where('id', $id)->update(['cantidad' => $data['cantidad'], 'updated_at' => now()]);

        $diff = $data['cantidad'] - $stockItem->cantidad;
        if ($diff !== 0) {
            DB::table('stock_historial')->insert([
                'producto' => $stockItem->producto,
                                'accion' => $diff > 0 ? 'suma' : 'resta',
                'cantidad' => abs($diff),
                'stock_id' => $stockItem->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->noContent();
    });
    Route::post('/stock', function (Request $r) {
        $data = $r->validate([
            'producto' => 'required|string|max:100',
            'cantidad' => 'required|integer|min:0',
                    ]);
        $id = DB::table('stock')->insertGetId($data + ['created_at' => now(), 'updated_at' => now()]);
        DB::table('stock_historial')->insert([
            'producto' => $data['producto'],
                        'accion' => 'agregado',
            'cantidad' => $data['cantidad'],
            'stock_id' => $id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return response()->noContent();
    });
    Route::delete('/stock/{id}', function (int $id) {
        $stockItem = DB::table('stock')->where('id', $id)->first();
        abort_unless($stockItem, 404);
        DB::table('stock')->where('id', $id)->delete();
        DB::table('stock_historial')->insert([
            'producto' => $stockItem->producto,
                        'accion' => 'quitado',
            'cantidad' => $stockItem->cantidad,
            'stock_id' => $stockItem->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return response()->noContent();
    });

    Route::get('/comandas', fn () => view('comandas', ['comandas' => comandasConProductos()]))->name('comandas');
    Route::get('/comandas/data', fn () => comandasConProductos());
    Route::post('/comandas', function () {
        $count = DB::table('comandas')->count();
        if ($count >= 50) {
            return response()->json(['message' => 'Se alcanzó el máximo de 50 comandas.'], 422);
        }

        $existing = DB::table('comandas')->pluck('mesa_numero')->all();
        $mesaNumero = null;
        for ($i = 1; $i <= 50; $i++) {
            if (!in_array($i, $existing, true)) {
                $mesaNumero = $i;
                break;
            }
        }
        abort_if(!$mesaNumero, 422, 'No hay números de mesa disponibles.');

        DB::table('comandas')->insert([
            'mesa_numero' => $mesaNumero,
            'mesa' => 'Mesa ' . $mesaNumero,
            'nombre' => 'Mesa ' . $mesaNumero,
            'estado' => 'abierta',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->noContent();
    });
    Route::delete('/comandas/{id}', function (int $id) {
        $hasProducts = DB::table('productos')->where('comanda_id', $id)->exists();
        if ($hasProducts) {
            return response()->json(['message' => 'No se puede quitar una comanda con productos cargados.'], 422);
        }
        DB::table('comandas')->where('id', $id)->delete();
        return response()->noContent();
    });
    Route::get('/comandas/historial/data', function (Request $request) {
        return historialComandasPaginado((int) $request->query('page', 1), 10);
    });

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
        DB::table('stock_historial')->insert([
            'producto' => $stockItem->producto,
                        'accion' => 'resta',
            'cantidad' => $data['cantidad'],
            'stock_id' => $stockItem->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->noContent();
    });
    Route::put('/productos/{id}', function (Request $r, int $id) { $data=$r->validate(['cantidad'=>'nullable|integer|min:1','notas'=>'nullable|string|max:255']); DB::table('productos')->where('id',$id)->update(array_filter($data, fn($v)=>$v!==null)+['updated_at'=>now()]); return response()->noContent(); });
    Route::delete('/productos/{id}', fn (int $id) => tap(response()->noContent(), fn()=>DB::table('productos')->where('id',$id)->delete()));
    Route::post('/comandas/{id}/cobrar', function (int $id) {
        $comanda = DB::table('comandas')->where('id', $id)->first();
        abort_unless($comanda, 404);

        $productos = DB::table('productos')->where('comanda_id', $id)->orderBy('id')->get();
        $historialId = DB::table('comandas_historial')->insertGetId([
            'comanda_id' => $comanda->id,
            'nombre' => $comanda->nombre,
            'mesa_numero' => $comanda->mesa_numero,
            'estado' => $comanda->estado,
            'cobrada_en' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($productos as $producto) {
            DB::table('productos_historial')->insert([
                'comanda_historial_id' => $historialId,
                'nombre' => $producto->nombre,
                'cantidad' => $producto->cantidad,
                'notas' => $producto->notas,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('productos')->where('comanda_id', $id)->delete();
        DB::table('comandas')->where('id', $id)->update([
            'nombre' => 'Mesa ' . $comanda->mesa_numero,
            'estado' => 'abierta',
            'updated_at' => now(),
        ]);

        return response()->noContent();
    });

    Route::get('/admin/graficas/data', function () {
        $from = now()->subDays(29)->startOfDay();
        $raw = DB::table('stock_historial')
            ->selectRaw('producto, DATE(created_at) as fecha, SUM(cantidad) as total')
            ->where('accion', 'resta')
            ->where('created_at', '>=', $from)
            ->groupBy('producto', DB::raw('DATE(created_at)'))
            ->orderBy('producto')
            ->orderBy('fecha')
            ->get();

        $productos = $raw->pluck('producto')->unique()->values();
        return $productos->map(function ($producto) use ($raw, $from) {
            $rows = $raw->where('producto', $producto)->keyBy('fecha');
            $series = collect(range(0, 29))->map(function ($offset) use ($from, $rows) {
                $date = $from->copy()->addDays($offset)->toDateString();
                return ['fecha' => $date, 'cantidad' => (int) optional($rows->get($date))->total];
            });
            return ['producto' => $producto, 'series' => $series];
        })->values();
    });

    Route::view('/admin', 'admin')->name('admin');
});
