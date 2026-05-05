@extends('layout')
@section('title', 'Stock')
@section('content')
<div class="rounded-xl border border-zinc-700 bg-zinc-900 p-6">
<h1 class="text-2xl font-semibold">Vista de stock</h1>
<table class="w-full mt-4 text-sm">
<thead class="text-zinc-200"><tr class="border-b border-zinc-600"><th class="text-left py-2">Producto</th><th class="text-left py-2">Cantidad</th><th class="text-left py-2">Unidad</th></tr></thead>
<tbody class="text-zinc-300" id="stockBody">
@foreach($stockItems as $item)
<tr class="border-b border-zinc-700"><td class="py-2">{{ $item->producto }}</td><td><input type="number" min="0" value="{{ $item->cantidad }}" class="w-24 rounded border border-zinc-600 bg-zinc-800 px-2 py-1" onchange="updateStock({{ $item->id }}, this.value)"></td><td>{{ $item->unidad }}</td></tr>
@endforeach
</tbody></table>
</div>
@endsection
@section('scripts')
<script>
async function refreshStock(){
    await fetch('/stock/data');
}
window.updateStock=async(id,cantidad)=>{
    await fetch(`/stock/${id}`,{method:'PUT',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({cantidad})});
    refreshStock();
}
refreshStock();
</script>
@endsection
