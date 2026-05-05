@extends('layout')
@section('title', 'Stock')
@section('content')
<div class="rounded-xl border border-zinc-800 bg-zinc-950 p-6">
<h1 class="text-2xl font-semibold">Vista de stock</h1>
<table class="w-full mt-4 text-sm">
<thead class="text-zinc-300"><tr class="border-b border-zinc-700"><th class="text-left py-2">Producto</th><th class="text-left py-2">Cantidad</th><th class="text-left py-2">Unidad</th></tr></thead>
<tbody class="text-zinc-400">
@foreach($stockItems as $item)
<tr class="border-b border-zinc-800"><td class="py-2">{{ $item->producto }}</td><td>{{ $item->cantidad }}</td><td>{{ $item->unidad }}</td></tr>
@endforeach
</tbody></table>
</div>
@endsection
