<?php

use App\Http\Middleware\RequireLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Barryvdh\DomPDF\Facade\Pdf;

function comandasConProductos()
{
    return DB::table('comandas')->orderBy('mesa_numero')->get()->map(function ($c) {
        $productos = DB::table('productos as p')
            ->leftJoin('stock as s', 's.producto', '=', 'p.nombre')
            ->where('p.comanda_id', $c->id)
            ->where(function ($q) {
                $q->whereNull('s.id')->orWhere('s.cantidad', '>', 0)->orWhere('s.ilimitado', true);
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

function formatearMonedaArs(float $importe): string
{
    $decimales = fmod(abs($importe), 1.0) > 0.00001 ? 2 : 0;
    return '$' . number_format($importe, $decimales, ',', '.');
}


function construirWhatsappUrl(?string $telefono): string
{
    $digits = preg_replace('/\D+/', '', $telefono ?? '');

    if (!$digits) {
        $digits = '5490000000000';
    }

    return 'https://wa.me/' . $digits;
}

function obtenerGaleriaImagenes()
{
    return DB::table('galeria_imagenes')
        ->orderByDesc('id')
        ->get(['id', 'titulo', 'categoria', 'ruta']);
}

function ticketLogoDataUri(): ?string
{
    $path = public_path('logo.png');
    if (!file_exists($path)) {
        return null;
    }

    $raw = file_get_contents($path);
    if ($raw === false) {
        return null;
    }

    if (function_exists('imagecreatefromstring') && function_exists('imagepng')) {
        $image = @imagecreatefromstring($raw);
        if ($image !== false) {
            imagefilter($image, IMG_FILTER_GRAYSCALE);
            imagefilter($image, IMG_FILTER_CONTRAST, -100);

            $width = imagesx($image);
            $height = imagesy($image);
            for ($y = 0; $y < $height; $y++) {
                for ($x = 0; $x < $width; $x++) {
                    $rgb = imagecolorat($image, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;
                    $luma = (int) round(($r * 0.299) + ($g * 0.587) + ($b * 0.114));
                    $bw = $luma > 170 ? 255 : 0;
                    $color = imagecolorallocate($image, $bw, $bw, $bw);
                    imagesetpixel($image, $x, $y, $color);
                }
            }

            ob_start();
            imagepng($image);
            $processed = ob_get_clean();
            imagedestroy($image);

            if ($processed !== false) {
                return 'data:image/png;base64,' . base64_encode($processed);
            }
        }
    }

    $mime = mime_content_type($path) ?: 'image/png';
    return 'data:' . $mime . ';base64,' . base64_encode($raw);
}

function qrPagoDataUri(?string $contenido): ?string
{
    $contenido = trim((string) $contenido);
    if ($contenido === '') {
        return null;
    }

    $url = 'https://api.qrserver.com/v1/create-qr-code/?format=png&size=220x220&margin=1&ecc=M&data=' . urlencode($contenido);
    try {
        $png = @file_get_contents($url);
        if (!$png) {
            return null;
        }

        return 'data:image/png;base64,' . base64_encode($png);
    } catch (\Throwable $e) {
        return null;
    }
}

function guardarComprobante58mm(object $comanda, $productos, float $total, string $telefono): string
{
    $tzNow = now()->setTimezone('America/Argentina/Buenos_Aires');
    $dir = storage_path('app/public/comprobantes');
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $alias = obtenerConfiguracion('payment_alias');
    $cbu = obtenerConfiguracion('payment_cbu');
    $showPaymentQr = obtenerConfiguracion('show_payment_qr', '0') === '1';
    $paymentValue = $alias ?: $cbu;
    $paymentLabel = $alias ? 'Alias' : 'CBU';
    $qrDataUri = $showPaymentQr && $paymentValue ? qrPagoDataUri($paymentValue) : null;

    $itemsCount = is_iterable($productos) ? count($productos) : 0;
    $paperHeight = min(1200, max(260, 180 + ($itemsCount * 34) + ($qrDataUri ? 170 : 0)));

    $pdf = Pdf::loadView('pdf.comprobante-comanda', [
        'comanda' => $comanda,
        'productos' => $productos,
        'total' => $total,
        'telefono' => $telefono,
        'ars' => fn (float $importe) => formatearMonedaArs($importe),
        'logoDataUri' => ticketLogoDataUri(),
        'fecha' => $tzNow,
        'paymentQrDataUri' => $qrDataUri,
        'paymentLabel' => $paymentLabel,
        'paymentValue' => $paymentValue,
    ])->setPaper([0, 0, 164.41, $paperHeight], 'portrait');

    $filename = 'comprobante-' . $tzNow->format('Ymd-His') . '-comanda-' . $comanda->id . '.pdf';
    file_put_contents($dir . DIRECTORY_SEPARATOR . $filename, $pdf->output());
    return 'storage/comprobantes/' . $filename;
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

function paginateCollection($items, int $page = 1, int $perPage = 10): array
{
    $total = $items->count();
    $lastPage = max(1, (int) ceil($total / $perPage));
    $page = min(max(1, $page), $lastPage);
    return [
        'data' => $items->forPage($page, $perPage)->values(),
        'current_page' => $page,
        'per_page' => $perPage,
        'total' => $total,
        'last_page' => $lastPage,
    ];
}

function resumenCierreCaja(int $comandasPage = 1, int $productosPage = 1, int $cierresPage = 1, bool $soloPendientes = true): array
{
    $historialQuery = DB::table('comandas_historial')->orderByDesc('cobrada_en');
    if ($soloPendientes) {
        $historialQuery->whereNull('cierre_caja_id');
    }
    $historial = $historialQuery->get();
    $historialIds = $historial->pluck('id');
    $productos = $historialIds->isEmpty() ? collect() : DB::table('productos_historial as ph')
        ->leftJoin('stock as s', 's.producto', '=', 'ph.nombre')
        ->whereIn('ph.comanda_historial_id', $historialIds)
        ->select('ph.*', 's.precio')
        ->get();
    $porComanda = $productos->groupBy('comanda_historial_id');
    $comandasIncluidas = $historial->map(function ($h) use ($porComanda) {
        $items = $porComanda->get($h->id, collect());
        return [
            'id' => $h->id,
            'nombre' => $h->nombre ?? ('Mesa ' . $h->mesa_numero),
            'cobrada_en' => $h->cobrada_en,
            'total' => round($items->sum(fn ($p) => ((float) ($p->precio ?? 0)) * (int) $p->cantidad), 2),
        ];
    })->values();
    $totalCobrado = $comandasIncluidas->sum('total');
    $productosVendidos = $productos->groupBy('nombre')->map(function ($group, $nombre) {
        $cantidad = (int) $group->sum('cantidad');
        $total = $group->sum(fn ($p) => ((float) ($p->precio ?? 0)) * (int) $p->cantidad);
        return ['producto' => $nombre, 'cantidad' => $cantidad, 'total' => round($total, 2)];
    })->values()->sortByDesc('total')->values();

    $historialCierres = DB::table('cierres_caja')
        ->select('id', 'created_at', 'responsable', 'total_cobrado', 'diferencia_efectivo')
        ->orderByDesc('created_at')
        ->get();
    return [
        'total_cobrado' => round($totalCobrado, 2),
        'comandas_cobradas' => $comandasIncluidas->count(),
        'productos_cobrados' => (int) $productos->sum('cantidad'),
        'comandas_abiertas' => DB::table('comandas')->count(),
        'fecha_hora_servidor' => now()->toIso8601String(),
        'productos_vendidos' => paginateCollection($productosVendidos, $productosPage, 10),
        'comandas_incluidas' => paginateCollection($comandasIncluidas, $comandasPage, 10),
        'historial_cierres' => paginateCollection($historialCierres, $cierresPage, 10),
    ];
}

Route::get('/', fn () => view('home', [
    'galeriaImagenes' => obtenerGaleriaImagenes(),
    'whatsappUrl' => construirWhatsappUrl(obtenerConfiguracion('telefono_local')),
]))->name('home');
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
    Route::get('/stock/historial/data', function (Request $request) {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 10;
        $total = DB::table('stock_historial')->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);

        $items = DB::table('stock_historial')
            ->orderByDesc('created_at')
            ->forPage($page, $perPage)
            ->get();

        return [
            'data' => $items,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => $lastPage,
        ];
    });
    Route::put('/stock/{id}', function (Request $r, int $id) {
        $data = $r->validate([
            'tipo' => 'nullable|in:producto,servicio',
            'cantidad' => 'nullable|integer|min:0|required_without:ilimitado',
            'ilimitado' => 'nullable|boolean',
            'precio' => 'required|numeric|min:0',
        ]);
        $stockItem = DB::table('stock')->where('id', $id)->first();
        abort_unless($stockItem, 404);
        $isUnlimited = array_key_exists('tipo', $data)
            ? $data['tipo'] === 'servicio'
            : (bool) ($data['ilimitado'] ?? false);
        $cantidadNueva = $isUnlimited ? 0 : (int) ($data['cantidad'] ?? 0);

        DB::table('stock')->where('id', $id)->update([
            'cantidad' => $cantidadNueva,
            'ilimitado' => $isUnlimited,
            'precio' => $data['precio'],
            'updated_at' => now(),
        ]);

        $diff = $cantidadNueva - $stockItem->cantidad;
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
            'tipo' => 'nullable|in:producto,servicio',
            'cantidad' => 'nullable|integer|min:0|required_without:ilimitado',
            'ilimitado' => 'nullable|boolean',
            'precio' => 'required|numeric|min:0',
        ]);
        $isUnlimited = array_key_exists('tipo', $data)
            ? $data['tipo'] === 'servicio'
            : (bool) ($data['ilimitado'] ?? false);
        $cantidadNueva = $isUnlimited ? 0 : (int) ($data['cantidad'] ?? 0);
        $id = DB::table('stock')->insertGetId([
            'producto' => $data['producto'],
            'cantidad' => $cantidadNueva,
            'ilimitado' => $isUnlimited,
            'precio' => $data['precio'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('stock_historial')->insert([
            'producto' => $data['producto'],
                        'accion' => 'agregado',
            'cantidad' => $cantidadNueva,
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
    Route::post('/comandas', function (Request $request) {
        $data = $request->validate([
            'nombre' => 'required|string|max:120',
            'cliente_documento' => 'nullable|string|max:40',
            'cliente_telefono' => 'nullable|string|max:40',
            'cliente_detalle' => 'nullable|string|max:255',
        ]);

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
            'nombre' => $data['nombre'],
            'cliente_documento' => $data['cliente_documento'] ?? null,
            'cliente_telefono' => $data['cliente_telefono'] ?? null,
            'cliente_detalle' => $data['cliente_detalle'] ?? null,
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
    Route::get('/comandas/cierre/data', function (Request $request) {
        return resumenCierreCaja(
            (int) $request->query('comandas_page', 1),
            (int) $request->query('productos_page', 1),
            (int) $request->query('cierres_page', 1),
        );
    });
    Route::post('/comandas/cierre/cerrar', function (Request $request) {
        $payload = $request->validate([
            'caja_inicial' => 'required|numeric|min:0',
            'efectivo_contado' => 'required|numeric|min:0',
            'responsable' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string|max:500',
            'movimientos' => 'nullable|array',
        ]);

        $dataCierre = DB::transaction(function () use ($payload) {
            $comandas = DB::table('comandas_historial')
                ->whereNull('cierre_caja_id')
                ->orderByDesc('cobrada_en')
                ->lockForUpdate()
                ->get();

            $historialIds = $comandas->pluck('id');
            $productos = $historialIds->isEmpty() ? collect() : DB::table('productos_historial as ph')
                ->leftJoin('stock as s', 's.producto', '=', 'ph.nombre')
                ->whereIn('ph.comanda_historial_id', $historialIds)
                ->select('ph.*', 's.precio')
                ->get();

            $totalCobrado = round($productos->sum(fn ($p) => ((float) ($p->precio ?? 0)) * (int) $p->cantidad), 2);
            $comandasCobradas = $comandas->count();
            $productosCobrados = (int) $productos->sum('cantidad');

            $mediosPago = collect($payload['movimientos'] ?? [])->filter(fn ($m) => is_array($m) || is_object($m));
            $totalEfectivoPeriodo = (float) $mediosPago
                ->filter(fn ($m) => strtolower((string) data_get($m, 'medio', data_get($m, 'name', ''))) === 'efectivo')
                ->sum(fn ($m) => (float) data_get($m, 'monto', data_get($m, 'amount', 0)));

            $efectivoEsperado = $totalCobrado;
            $diferencia = (float) $payload['efectivo_contado'] - $efectivoEsperado;

            $resumen = [
                'total_cobrado' => $totalCobrado,
                'comandas_cobradas' => $comandasCobradas,
                'productos_cobrados' => $productosCobrados,
                'comandas_abiertas' => DB::table('comandas')->count(),
                'efectivo_periodo' => $totalEfectivoPeriodo,
            ];

            $id = DB::table('cierres_caja')->insertGetId([
                'total_cobrado' => $resumen['total_cobrado'],
                'comandas_cobradas' => $resumen['comandas_cobradas'],
                'productos_cobrados' => $resumen['productos_cobrados'],
                'comandas_abiertas' => $resumen['comandas_abiertas'],
                'turno' => 'Diario',
                'responsable' => $payload['responsable'] ?? null,
                'caja_inicial' => $payload['caja_inicial'],
                'efectivo_esperado' => $efectivoEsperado,
                'efectivo_contado' => $payload['efectivo_contado'],
                'diferencia_efectivo' => $diferencia,
                'observaciones' => $payload['observaciones'] ?? null,
                'detalle' => json_encode([...$resumen, 'movimientos' => $payload['movimientos'] ?? []]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($historialIds->isNotEmpty()) {
                DB::table('comandas_historial')->whereIn('id', $historialIds)->update([
                    'cierre_caja_id' => $id,
                    'updated_at' => now(),
                ]);
            }

            return [$id, [...$resumen, ...$payload, 'efectivo_esperado' => $efectivoEsperado, 'diferencia_efectivo' => $diferencia]];
        });

        [$id, $cierre] = $dataCierre;
        $file = 'cierre-caja-' . now()->format('Ymd-His') . '.pdf';
        $dir = storage_path('app/public/comprobantes');
        if (!is_dir($dir)) { mkdir($dir, 0775, true); }
        $pdf = Pdf::loadView('pdf.cierre-caja', [
            'cierre' => $cierre,
            'ars' => fn (float $importe) => formatearMonedaArs($importe),
            'logoDataUri' => ticketLogoDataUri(),
        ])->setPaper([0, 0, 226.77, 1400], 'portrait');
        file_put_contents($dir . DIRECTORY_SEPARATOR . $file, $pdf->output());
        $path = 'storage/comprobantes/' . $file;
        return response()->json(['id' => $id, 'comprobante_path' => $path]);
    });

    Route::post('/productos', function (Request $r) {
        $data = $r->validate([
            'comanda_id' => 'required|exists:comandas,id',
            'stock_id' => 'required|exists:stock,id',
            'cantidad' => 'required|integer|min:1',
            'notas' => 'nullable|string|max:255',
        ]);

        $stockItem = DB::table('stock')->where('id', $data['stock_id'])->first();
        if (!$stockItem || (!$stockItem->ilimitado && $stockItem->cantidad < $data['cantidad'])) {
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

        if (!$stockItem->ilimitado) {
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
        }

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
        $pdfPath = guardarComprobante58mm($comanda, $productos, $total, $telefonoLocal);

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
        DB::table('comandas')->where('id', $id)->delete();

        return response()->json(['comprobante_path' => $pdfPath]);
    });

    Route::get('/comandas/{id}/ticket-58mm', function (int $id) {
        $comanda = DB::table('comandas')->where('id', $id)->first();
        abort_unless($comanda, 404);

        $productos = DB::table('productos as p')
            ->leftJoin('stock as s', 's.producto', '=', 'p.nombre')
            ->where('p.comanda_id', $id)
            ->orderBy('p.id')
            ->select('p.*', 's.precio')
            ->get();

        $subtotal = $productos->sum(fn ($p) => ((float) ($p->precio ?? 0)) * (int) $p->cantidad);
        $descuento = 0;
        $total = $subtotal - $descuento;

        return view('tickets.comprobante', [
            'comanda' => $comanda,
            'productos' => $productos,
                'descuento' => $descuento,
            'total' => $total,
            'telefono' => obtenerConfiguracion('telefono_local'),
                'ars' => fn (float $importe) => formatearMonedaArs($importe),
            'adminAlias' => obtenerConfiguracion('admin_alias'),
            'adminAliasQrUrl' => ($alias = obtenerConfiguracion('admin_alias')) ? ('https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode($alias)) : null,
        ]);
    })->name('comandas.ticket58');

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

    Route::get('/admin/configuracion', fn () => [
        'telefono_local' => obtenerConfiguracion('telefono_local', '+54 9 11 0000-0000'),
        'admin_alias' => obtenerConfiguracion('admin_alias'),
        'payment_alias' => obtenerConfiguracion('payment_alias'),
        'payment_cbu' => obtenerConfiguracion('payment_cbu'),
        'payment_holder' => obtenerConfiguracion('payment_holder'),
        'payment_bank' => obtenerConfiguracion('payment_bank'),
        'show_payment_qr' => obtenerConfiguracion('show_payment_qr', '0') === '1',
    ]);
    Route::put('/admin/configuracion/telefono', function (Request $r) {
        $data = $r->validate([
            'telefono_local' => 'required|string|max:50',
            'admin_alias' => 'nullable|string|max:80',
            'payment_alias' => 'nullable|string|max:120',
            'payment_cbu' => 'nullable|string|max:40',
            'payment_holder' => 'nullable|string|max:120',
            'payment_bank' => 'nullable|string|max:120',
            'show_payment_qr' => 'nullable|boolean',
        ]);

        if (($data['show_payment_qr'] ?? false) && empty(trim((string) ($data['payment_alias'] ?? ''))) && empty(trim((string) ($data['payment_cbu'] ?? '')))) {
            return response()->json(['message' => 'Para mostrar QR debe cargar Alias o CBU.'], 422);
        }

        $configuraciones = [
            'telefono_local' => $data['telefono_local'],
            'admin_alias' => $data['admin_alias'] ?? null,
            'payment_alias' => $data['payment_alias'] ?? null,
            'payment_cbu' => $data['payment_cbu'] ?? null,
            'payment_holder' => $data['payment_holder'] ?? null,
            'payment_bank' => $data['payment_bank'] ?? null,
            'show_payment_qr' => ($data['show_payment_qr'] ?? false) ? '1' : '0',
        ];

        foreach ($configuraciones as $clave => $valor) {
            DB::table('configuraciones')->updateOrInsert(
                ['clave' => $clave],
                ['valor' => $valor, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        return response()->noContent();
    });


    Route::get('/admin/base-datos/tablas', function () {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $tables = collect(DB::select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'"))->pluck('name');
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            $tables = collect(DB::select('SHOW TABLES'))->map(fn ($row) => (array) $row)->map(fn ($row) => array_values($row)[0]);
        } elseif ($driver === 'pgsql') {
            $tables = collect(DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'"))->pluck('tablename');
        } else {
            return response()->json(['message' => 'Motor de base de datos no soportado para esta función.'], 422);
        }

        return $tables->sort()->values();
    });

    Route::get('/admin/base-datos/tabla/{tabla}/descargar', function (string $tabla) {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $tablas = collect(DB::select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'"))->pluck('name')->all();
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            $tablas = collect(DB::select('SHOW TABLES'))->map(fn ($row) => (array) $row)->map(fn ($row) => array_values($row)[0])->all();
        } elseif ($driver === 'pgsql') {
            $tablas = collect(DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'"))->pluck('tablename')->all();
        } else {
            $tablas = [];
        }

        if (!in_array($tabla, $tablas, true)) {
            return response()->json(['message' => 'Tabla inválida.'], 404);
        }

        $rows = DB::table($tabla)->get();
        return response()->json(['tabla' => $tabla, 'rows' => $rows], 200, [
            'Content-Disposition' => 'attachment; filename="' . $tabla . '.json"',
        ]);
    });

    Route::post('/admin/base-datos/tabla/{tabla}/cargar', function (Request $request, string $tabla) {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $tablas = collect(DB::select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'"))->pluck('name')->all();
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            $tablas = collect(DB::select('SHOW TABLES'))->map(fn ($row) => (array) $row)->map(fn ($row) => array_values($row)[0])->all();
        } elseif ($driver === 'pgsql') {
            $tablas = collect(DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'"))->pluck('tablename')->all();
        } else {
            $tablas = [];
        }

        if (!in_array($tabla, $tablas, true)) {
            return response()->json(['message' => 'Tabla inválida.'], 404);
        }

        $rows = $request->validate(['rows' => 'required|array'])['rows'];

        DB::transaction(function () use ($tabla, $rows) {
            DB::table($tabla)->delete();
            foreach ($rows as $row) {
                DB::table($tabla)->insert((array) $row);
            }
        });

        return response()->noContent();
    });

    Route::delete('/admin/base-datos/tabla/{tabla}', function (string $tabla) {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $tablas = collect(DB::select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'"))->pluck('name')->all();
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            $tablas = collect(DB::select('SHOW TABLES'))->map(fn ($row) => (array) $row)->map(fn ($row) => array_values($row)[0])->all();
        } elseif ($driver === 'pgsql') {
            $tablas = collect(DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'"))->pluck('tablename')->all();
        } else {
            $tablas = [];
        }

        if (!in_array($tabla, $tablas, true)) {
            return response()->json(['message' => 'Tabla inválida.'], 404);
        }

        DB::table($tabla)->delete();

        return response()->noContent();
    });

    Route::get('/admin/base-datos/descargar', function () {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $tablas = collect(DB::select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'"))->pluck('name');
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            $tablas = collect(DB::select('SHOW TABLES'))->map(fn ($row) => (array) $row)->map(fn ($row) => array_values($row)[0]);
        } elseif ($driver === 'pgsql') {
            $tablas = collect(DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'"))->pluck('tablename');
        } else {
            return response()->json(['message' => 'Motor de base de datos no soportado para esta función.'], 422);
        }

        $payload = $tablas->values()->mapWithKeys(fn ($tabla) => [$tabla => DB::table($tabla)->get()]);

        return response()->json(['tablas' => $payload], 200, [
            'Content-Disposition' => 'attachment; filename="base_de_datos.json"',
        ]);
    });

    Route::post('/admin/base-datos/cargar', function (Request $request) {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $tablas = collect(DB::select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'"))->pluck('name')->all();
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            $tablas = collect(DB::select('SHOW TABLES'))->map(fn ($row) => (array) $row)->map(fn ($row) => array_values($row)[0])->all();
        } elseif ($driver === 'pgsql') {
            $tablas = collect(DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'"))->pluck('tablename')->all();
        } else {
            return response()->json(['message' => 'Motor de base de datos no soportado para esta función.'], 422);
        }

        $incoming = $request->validate(['tablas' => 'required|array'])['tablas'];

        DB::transaction(function () use ($incoming, $tablas) {
            foreach ($tablas as $tabla) {
                if (!array_key_exists($tabla, $incoming)) {
                    continue;
                }
                DB::table($tabla)->delete();
                foreach ((array) $incoming[$tabla] as $row) {
                    DB::table($tabla)->insert((array) $row);
                }
            }
        });

        return response()->noContent();
    });

    Route::delete('/admin/base-datos', function () {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $tablas = collect(DB::select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'"))->pluck('name');
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            $tablas = collect(DB::select('SHOW TABLES'))->map(fn ($row) => (array) $row)->map(fn ($row) => array_values($row)[0]);
        } elseif ($driver === 'pgsql') {
            $tablas = collect(DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'"))->pluck('tablename');
        } else {
            return response()->json(['message' => 'Motor de base de datos no soportado para esta función.'], 422);
        }

        DB::transaction(function () use ($tablas) {
            foreach ($tablas as $tabla) {
                DB::table($tabla)->delete();
            }
        });

        return response()->noContent();
    });

    Route::view('/admin', 'admin')->name('admin');
});
