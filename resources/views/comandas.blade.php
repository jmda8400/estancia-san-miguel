@extends('layout')
@section('title', 'Comandas')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Sistema de comandas</h1>
<div class="grid gap-4 md:grid-cols-2">
    <div class="rounded-xl border border-zinc-700 bg-zinc-900 p-4">
        <h2 class="font-semibold mb-3">Mesas</h2>
        <div id="tables" class="grid grid-cols-2 sm:grid-cols-3 gap-2"></div>
    </div>
    <div class="rounded-xl border border-zinc-700 bg-zinc-900 p-4">
        <h2 id="selectedTitle" class="font-semibold mb-3">Seleccione una mesa</h2>
        <div id="productsList" class="space-y-2"></div>
    </div>
    <div class="rounded-xl border border-zinc-700 bg-zinc-900 p-4 md:col-span-2">
        <h2 class="font-semibold mb-3">Agregar producto desde stock</h2>
        <form id="newProductForm" class="grid gap-2 md:grid-cols-4">
            <select id="stockItem" class="rounded border border-zinc-600 bg-zinc-800 px-3 py-2" required></select>
            <input id="productQty" type="number" min="1" value="1" class="rounded border border-zinc-600 bg-zinc-800 px-3 py-2" required>
            <input id="productNotes" class="rounded border border-zinc-600 bg-zinc-800 px-3 py-2" placeholder="Notas">
            <button class="rounded bg-zinc-100 text-zinc-900 px-3 py-2">Agregar a comanda</button>
        </form>
    </div>
</div>
@endsection
@section('scripts')
<script>
let state = { selectedComandaId: null, comandas: @json($comandas), stock: [] };
const tablesEl = document.getElementById('tables');
const productsEl = document.getElementById('productsList');
const titleEl = document.getElementById('selectedTitle');
const stockEl = document.getElementById('stockItem');
function render() {
 tablesEl.innerHTML = state.comandas.map(c => `<button class="text-left rounded border px-3 py-2 ${state.selectedComandaId===c.id?'border-zinc-100 bg-zinc-700':'border-zinc-600 bg-zinc-800'}" onclick="selectComanda(${c.id})">Mesa ${c.mesa_numero}<br><small>${c.productos.length} productos</small></button>`).join('');
 const comanda = state.comandas.find(c=>c.id===state.selectedComandaId);
 if(!comanda){titleEl.textContent='Seleccione una mesa'; productsEl.innerHTML=''; return;}
 titleEl.textContent = `Productos de Mesa ${comanda.mesa_numero}`;
 productsEl.innerHTML = comanda.productos.map(p=>`<div class="flex gap-2 items-center"><strong>${p.nombre}</strong><input type="number" min="1" value="${p.cantidad}" onchange="updateProducto(${p.id},{cantidad:this.value})" class="w-16 rounded border border-zinc-600 bg-zinc-800 px-2 py-1"><input value="${p.notas??''}" onchange="updateProducto(${p.id},{notas:this.value})" class="rounded border border-zinc-600 bg-zinc-800 px-2 py-1"><button class="rounded px-2 py-1 bg-zinc-700 hover:bg-zinc-600" title="Quitar producto" onclick="deleteProducto(${p.id})">🗑️</button></div>`).join('');
 stockEl.innerHTML = state.stock.map(s => `<option value="${s.id}" ${s.cantidad < 1 ? 'disabled' : ''}>${s.producto} (${s.cantidad} ${s.unidad})</option>`).join('');
}
window.selectComanda=(id)=>{state.selectedComandaId=id;render();}
async function refresh(){
 const [c, s] = await Promise.all([fetch('/comandas/data'), fetch('/stock/data')]);
 state.comandas = await c.json();
 state.stock = await s.json();
 render();
}
window.updateProducto=async(id,data)=>{await fetch(`/productos/${id}`,{method:'PUT',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify(data)}); refresh();}
window.deleteProducto=async(id)=>{await fetch(`/productos/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}); refresh();}
document.getElementById('newProductForm').onsubmit=async(e)=>{
 e.preventDefault();
 if(!state.selectedComandaId){alert('Seleccione una mesa');return;}
 const response = await fetch('/productos',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({comanda_id:state.selectedComandaId,stock_id:stockItem.value,cantidad:productQty.value,notas:productNotes.value})});
 if(!response.ok){ const data = await response.json(); alert(data.message ?? 'No se pudo agregar el producto.'); return; }
 e.target.reset(); productQty.value=1; refresh();
};
refresh();
</script>
@endsection
