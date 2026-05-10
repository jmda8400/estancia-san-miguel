<?php

use App\Http\Middleware\RequireLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

function telefonoComprobante(): string
{
    $path = storage_path('app/config/telefono.txt');
    if (!File::exists($path)) {
        return '';
    }

    return trim((string) File::get($path));
}

function guardarTelefonoComprobante(string $telefono): void
{
    $dir = storage_path('app/config');
    if (!File::isDirectory($dir)) {
        File::makeDirectory($dir, 0755, true);
    }

    File::put($dir . '/telefono.txt', trim($telefono));
}

function generarPdfTicketCobro(object $comanda, $productos, string $telefono, string $fechaHora): string
{
    $anchoMm = 80;
    $anchoPt = $anchoMm * 2.83465;
    $lineas = [];
    $lineas[] = 'ESTANCIA SAN MIGUEL';
    $lineas[] = 'Logo: /public/logo.png';
    $lineas[] = $fechaHora;
    $lineas[] = $telefono !== '' ? ('Tel: ' . $telefono) : 'Tel: -';
    $lineas[] = str_repeat('-', 40);

    $subtotal = 0.0;
    foreach ($productos as $producto) {
        $precioUnitario = (float) (DB::table('stock')->where('producto', $producto->nombre)->value('precio') ?? 0);
        $totalItem = $precioUnitario * (int) $producto->cantidad;
        $subtotal += $totalItem;
        $lineas[] = sprintf('%s x%d $%0.2f', $producto->nombre, $producto->cantidad, $totalItem);
    }

    $lineas[] = str_repeat('-', 40);
    $lineas[] = sprintf('Subtotal: $%0.2f', $subtotal);
    $lineas[] = sprintf('Total: $%0.2f', $subtotal);

    $altoPt = max(300, (count($lineas) * 16) + 60);
    $stream = "BT\n/F1 9 Tf\n10 " . ($altoPt - 20) . " Td\n";
    foreach ($lineas as $linea) {
        $safe = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $linea);
        $stream .= '(' . $safe . ") Tj\n0 -14 Td\n";
    }
    $stream .= "ET\n";

    $pdf = "%PDF-1.4\n";
    $offsets = [];
    $addObj = function (string $obj) use (&$pdf, &$offsets) {
        $offsets[] = strlen($pdf);
        $pdf .= (count($offsets)) . " 0 obj\n" . $obj . "\nendobj\n";
    };

    $addObj('<< /Type /Catalog /Pages 2 0 R >>');
    $addObj('<< /Type /Pages /Kids [3 0 R] /Count 1 >>');
    $addObj('<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' . number_format($anchoPt, 2, '.', '') . ' ' . number_format($altoPt, 2, '.', '') . '] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>');
    $addObj('<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . 'endstream');
    $addObj('<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>');

    $xrefPos = strlen($pdf);
    $pdf .= "xref\n0 " . (count($offsets) + 1) . "\n0000000000 65535 f \n";
    foreach ($offsets as $off) {
        $pdf .= sprintf("%010d 00000 n \n", $off);
    }
    $pdf .= "trailer\n<< /Size " . (count($offsets) + 1) . " /Root 1 0 R >>\nstartxref\n" . $xrefPos . "\n%%EOF";

    $dir = storage_path('app/public/comprobantes');
    if (!File::isDirectory($dir)) {
        File::makeDirectory($dir, 0755, true);
    }
    $filename = 'comanda-' . $comanda->id . '-' . now()->format('Ymd-His') . '.pdf';
    File::put($dir . '/' . $filename, $pdf);

    return 'storage/comprobantes/' . $filename;
}

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
            ->select('p.*', 's.precio')
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
        $data = $r->validate([
            'cantidad' => 'required|integer|min:0',
            'precio' => 'required|numeric|min:0',
        ]);
        $stockItem = DB::table('stock')->where('id', $id)->first();
        abort_unless($stockItem, 404);

        DB::table('stock')->where('id', $id)->update([
            'cantidad' => $data['cantidad'],
            'precio' => $data['precio'],
            'updated_at' => now(),
        ]);

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
            'precio' => 'required|numeric|min:0',
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

        $existingProducto = DB::table('productos')
            ->where('comanda_id', $data['comanda_id'])
            ->where('nombre', $stockItem->producto)
            ->first();

        if ($existingProducto) {
            DB::table('productos')->where('id', $existingProducto->id)->update([
                'cantidad' => $existingProducto->cantidad + $data['cantidad'],
                'notas' => $data['notas'] ?? $existingProducto->notas,
                'updated_at' => now(),
            ]);
        } else {
            DB::table('productos')->insert([
                'comanda_id' => $data['comanda_id'],
                'nombre' => $stockItem->producto,
                'cantidad' => $data['cantidad'],
                'notas' => $data['notas'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

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

        $fechaHora = now()->setTimezone('America/Argentina/Buenos_Aires')->format('d/m/Y H:i:s');
        $comprobantePath = generarPdfTicketCobro($comanda, $productos, telefonoComprobante(), $fechaHora);

        DB::table('productos')->where('comanda_id', $id)->delete();
        DB::table('comandas')->where('id', $id)->update([
            'nombre' => 'Mesa ' . $comanda->mesa_numero,
            'estado' => 'abierta',
            'updated_at' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'comprobante_pdf' => $comprobantePath,
            'carpeta' => 'storage/app/public/comprobantes',
        ]);
    });

    Route::get('/admin/graficas/data', function (Request $request) {
        $end = $request->query('end') ? \Carbon\Carbon::parse($request->query('end'))->endOfDay() : now()->endOfDay();
        $start = $request->query('start') ? \Carbon\Carbon::parse($request->query('start'))->startOfDay() : $end->copy()->subDays(29)->startOfDay();
        if ($start->greaterThan($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }
        $days = max(1, min(120, (int) $start->diffInDays($end) + 1));
        $start = $end->copy()->subDays($days - 1)->startOfDay();
        $dates = collect(range(0, $days - 1))->map(fn ($offset) => $start->copy()->addDays($offset)->toDateString());

        $stockItems = DB::table('stock')->select('id', 'producto', 'cantidad')->orderBy('id')->get();
        $history = DB::table('stock_historial')
            ->selectRaw('stock_id, DATE(created_at) as fecha, accion, SUM(cantidad) as total')
            ->whereIn('accion', ['suma', 'resta'])
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('stock_id', DB::raw('DATE(created_at)'), 'accion')
            ->orderBy('fecha')
            ->get();

        $series = $stockItems->map(function ($item) use ($dates, $history) {
            $productHistory = $history->where('stock_id', $item->id);
            $initial = (int) $item->cantidad;

            foreach ($productHistory as $entry) {
                $delta = (int) $entry->total;
                $initial += $entry->accion === 'resta' ? $delta : -$delta;
            }

            $running = $initial;
            $points = $dates->map(function ($date) use (&$running, $productHistory) {
                $daily = $productHistory->where('fecha', $date);
                $plus = (int) optional($daily->firstWhere('accion', 'suma'))->total;
                $minus = (int) optional($daily->firstWhere('accion', 'resta'))->total;
                $running += $plus - $minus;

                return [
                    'fecha' => $date,
                    'cantidad' => $running,
                ];
            })->values();

            return [
                'producto' => $item->producto,
                'puntos' => $points,
            ];
        })->values();

        return ['days' => $days, 'start' => $start->toDateString(), 'end' => $end->toDateString(), 'series' => $series];
    });

    Route::view('/admin', 'admin')->name('admin');
    Route::get('/admin/config/telefono', fn () => ['telefono' => telefonoComprobante()]);
    Route::put('/admin/config/telefono', function (Request $request) {
        $data = $request->validate([
            'telefono' => 'nullable|string|max:40',
        ]);
        guardarTelefonoComprobante($data['telefono'] ?? '');
        return response()->json(['ok' => true]);
    });
});
