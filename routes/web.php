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
            ->select('p.*', 's.precio')
            ->get();

        return (array) $c + ['productos' => $productos];
    });
}


function obtenerConfiguracion(string $clave, ?string $default = null): ?string
{
    return DB::table('configuraciones')->where('clave', $clave)->value('valor') ?? $default;
}

function guardarTicketPdf80mm(object $comanda, $productos, float $subtotal, float $total, string $telefono): string
{
    $tzNow = now()->setTimezone('America/Argentina/Buenos_Aires');
    $dir = storage_path('app/public/tickets-cobrados');
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $logoPath = public_path('logo.png');
    $ticketWidth = 226.77; // 80mm
    $lineHeight = 13;
    $marginX = 12;
    $startY = 120;
    $lines = [];
    $lines[] = 'Fecha - Hora: ' . $tzNow->format('d/m/Y H:i:s');
    $lines[] = 'Telefono: ' . $telefono;
    $lines[] = str_repeat('-', 36);
    foreach ($productos as $producto) {
        $precioUnitario = (float) ($producto->precio ?? 0);
        $importe = $precioUnitario * (int) $producto->cantidad;
        $lines[] = sprintf('%s x%d  $%0.2f', $producto->nombre, $producto->cantidad, $importe);
    }
    $lines[] = str_repeat('-', 36);
    $lines[] = sprintf('Subtotal: $%0.2f', $subtotal);
    $lines[] = sprintf('Total: $%0.2f', $total);

    $contentHeight = $startY + (count($lines) * $lineHeight) + 20;
    $pdfHeight = max(350, $contentHeight);

    $tmpLogo = tempnam(sys_get_temp_dir(), 'logo_ticket_') . '.jpg';
    $logoCreated = false;
    if (file_exists($logoPath)) {
        $img = @imagecreatefrompng($logoPath);
        if ($img !== false) {
            imagefilter($img, IMG_FILTER_GRAYSCALE);
            imagejpeg($img, $tmpLogo, 85);
            imagedestroy($img);
            $logoCreated = true;
        }
    }

    $objects = [];
    $content = "BT /F1 10 Tf
";
    $y = $startY;
    foreach ($lines as $line) {
        $safe = str_replace(['\\', '(', ')'], ['\\\\', '\(', '\)'], $line);
        $content .= sprintf("1 0 0 1 %.2f %.2f Tm (%s) Tj
", $marginX, $pdfHeight - $y, $safe);
        $y += $lineHeight;
    }
    $content .= "ET
";

    $imageObjNum = null;
    if ($logoCreated) {
        $jpg = file_get_contents($tmpLogo);
        [$w, $h] = getimagesize($tmpLogo);
        $imageObjNum = 5;
        $objects[5] = "<< /Type /XObject /Subtype /Image /Width $w /Height $h /ColorSpace /DeviceGray /BitsPerComponent 8 /Filter /DCTDecode /Length " . strlen($jpg) . " >>
stream
" . $jpg . "
endstream";
        $drawW = 120;
        $drawH = max(28, ($h / max($w,1)) * $drawW);
        $content = "q $drawW 0 0 $drawH 53 " . ($pdfHeight - 90) . " cm /Im1 Do Q
" . $content;
    }

    $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
    $objects[2] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
    $res = $imageObjNum ? "<< /Font << /F1 4 0 R >> /XObject << /Im1 5 0 R >> >>" : "<< /Font << /F1 4 0 R >> >>";
    $objects[3] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 $ticketWidth $pdfHeight] /Resources $res /Contents 6 0 R >>";
    $objects[4] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
    $objects[6] = "<< /Length " . strlen($content) . " >>
stream
$content
endstream";

    $pdf = "%PDF-1.4
";
    $offsets = [0];
    for ($i=1; $i<=6; $i++) {
        if (!isset($objects[$i])) continue;
        $offsets[$i] = strlen($pdf);
        $pdf .= "$i 0 obj
" . $objects[$i] . "
endobj
";
    }
    $xref = strlen($pdf);
    $pdf .= "xref
0 7
0000000000 65535 f 
";
    for ($i=1; $i<=6; $i++) {
        $off = $offsets[$i] ?? 0;
        $pdf .= sprintf("%010d 00000 n 
", $off);
    }
    $pdf .= "trailer
<< /Size 7 /Root 1 0 R >>
startxref
$xref
%%EOF";

    $file = 'ticket-' . $tzNow->format('Ymd-His') . '-comanda-' . $comanda->id . '.pdf';
    file_put_contents($dir . DIRECTORY_SEPARATOR . $file, $pdf);
    if ($logoCreated && file_exists($tmpLogo)) {
        unlink($tmpLogo);
    }

    return 'storage/tickets-cobrados/' . $file;
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

        $productos = DB::table('productos as p')
            ->leftJoin('stock as s', 's.producto', '=', 'p.nombre')
            ->where('p.comanda_id', $id)
            ->orderBy('p.id')
            ->select('p.*', 's.precio')
            ->get();
        $subtotal = $productos->sum(fn ($p) => ((float) ($p->precio ?? 0)) * (int) $p->cantidad);
        $total = $subtotal;
        $telefonoLocal = obtenerConfiguracion('telefono_local', '+54 9 11 0000-0000');
        $pdfPath = guardarTicketPdf80mm($comanda, $productos, $subtotal, $total, $telefonoLocal);

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

        return response()->json(['pdf_path' => $pdfPath]);
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

    Route::get('/admin/configuracion', fn () => ['telefono_local' => obtenerConfiguracion('telefono_local', '+54 9 11 0000-0000')]);
    Route::put('/admin/configuracion/telefono', function (Request $r) {
        $data = $r->validate(['telefono_local' => 'required|string|max:50']);
        DB::table('configuraciones')->updateOrInsert(
            ['clave' => 'telefono_local'],
            ['valor' => $data['telefono_local'], 'updated_at' => now(), 'created_at' => now()]
        );
        return response()->noContent();
    });

    Route::view('/admin', 'admin')->name('admin');
});
