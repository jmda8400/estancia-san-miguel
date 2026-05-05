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

<div class="rounded-xl border border-zinc-700 bg-zinc-900 p-4 mt-4">
    <h2 class="font-semibold mb-3">Agregar producto a una comanda (desde stock)</h2>
    <form id="newProductForm" class="grid gap-2 md:grid-cols-5">
        <select id="comandaId" class="rounded border border-zinc-600 bg-zinc-800 px-3 py-2" required>
            <option value="">Seleccione comanda</option>
            @foreach($comandas as $comanda)
            <option value="{{ $comanda->id }}">{{ $comanda->nombre ?? ('Mesa '.$comanda->mesa_numero) }}</option>
            @endforeach
        </select>
        <select id="stockItem" class="rounded border border-zinc-600 bg-zinc-800 px-3 py-2" required></select>
        <input id="productQty" type="number" min="1" value="1" class="rounded border border-zinc-600 bg-zinc-800 px-3 py-2" required>
        <input id="productNotes" class="rounded border border-zinc-600 bg-zinc-800 px-3 py-2" placeholder="Notas">
        <button class="rounded bg-zinc-100 text-zinc-900 px-3 py-2">Agregar a comanda</button>
    </form>
</div>
@endsection
@section('scripts')
<script>
let stock = [];
async function refreshStock(){
    const response = await fetch('/stock/data');
    stock = await response.json();
    document.getElementById('stockItem').innerHTML = stock.map(s => `<option value="${s.id}">${s.producto} (${s.cantidad} ${s.unidad})</option>`).join('');
}
window.updateStock=async(id,cantidad)=>{
    await fetch(`/stock/${id}`,{method:'PUT',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({cantidad})});
    refreshStock();
}
document.getElementById('newProductForm').onsubmit=async(e)=>{
 e.preventDefault();
 const response = await fetch('/productos',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({comanda_id:comandaId.value,stock_id:stockItem.value,cantidad:productQty.value,notas:productNotes.value})});
 if(!response.ok){ const data = await response.json(); alert(data.message ?? 'No se pudo agregar el producto.'); return; }
 e.target.reset(); productQty.value=1; refreshStock();
};
refreshStock();
</script>
@endsection
