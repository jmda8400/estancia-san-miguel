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
        <div class="flex justify-end mt-4">
            <button id="chargeBtn" class="hidden rounded bg-green-600 hover:bg-green-500 text-white px-4 py-2" onclick="cobrarComanda()">Cobrar</button>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
let state = { selectedComandaId: null, comandas: @json($comandas) };
const tablesEl = document.getElementById('tables');
const productsEl = document.getElementById('productsList');
const titleEl = document.getElementById('selectedTitle');
const chargeBtn = document.getElementById('chargeBtn');
function render() {
 tablesEl.innerHTML = state.comandas.map(c => `<button class="text-left rounded border px-3 py-2 ${state.selectedComandaId===c.id?'border-zinc-100 bg-zinc-700':'border-zinc-600 bg-zinc-800'}" onclick="selectComanda(${c.id})">${c.nombre ?? ('Mesa ' + c.mesa_numero)}<br><small>${c.productos.length} productos</small></button>`).join('');
 const comanda = state.comandas.find(c=>c.id===state.selectedComandaId);
 if(!comanda){titleEl.textContent='Seleccione una mesa'; productsEl.innerHTML=''; chargeBtn.classList.add('hidden'); return;}
 titleEl.textContent = `Productos de ${comanda.nombre ?? ('Mesa ' + comanda.mesa_numero)}`;
 chargeBtn.classList.remove('hidden');
 productsEl.innerHTML = comanda.productos.map(p=>`<div class="flex gap-2 items-center"><strong>${p.nombre}</strong><input type="number" min="1" value="${p.cantidad}" onchange="updateProducto(${p.id},{cantidad:this.value})" class="w-16 rounded border border-zinc-600 bg-zinc-800 px-2 py-1"><input value="${p.notas??''}" onchange="updateProducto(${p.id},{notas:this.value})" class="rounded border border-zinc-600 bg-zinc-800 px-2 py-1"><button class="rounded px-2 py-1 bg-zinc-700 hover:bg-zinc-600" title="Quitar producto" onclick="deleteProducto(${p.id})">🗑️</button></div>`).join('');
}
window.selectComanda=(id)=>{state.selectedComandaId=id;render();}
async function refresh(){
 const c = await fetch('/comandas/data');
 state.comandas = await c.json();
 render();
}
window.updateProducto=async(id,data)=>{await fetch(`/productos/${id}`,{method:'PUT',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify(data)}); refresh();}
window.deleteProducto=async(id)=>{await fetch(`/productos/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}); refresh();}
window.cobrarComanda=async()=>{
 if(!state.selectedComandaId){return;}
 await fetch(`/comandas/${state.selectedComandaId}/cobrar`,{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}});
 refresh();
};
refresh();
</script>
@endsection
