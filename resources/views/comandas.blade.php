@extends('layout')
@section('title', 'Comandas')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Sistema de comandas</h1>
<div class="grid gap-4 md:grid-cols-2">
    <div class="rounded-xl border border-zinc-800 bg-zinc-950 p-4">
        <h2 class="font-semibold mb-3">Mesas</h2>
        <div id="tables" class="grid grid-cols-2 sm:grid-cols-3 gap-2"></div>
    </div>
    <div class="rounded-xl border border-zinc-800 bg-zinc-950 p-4">
        <h2 id="selectedTitle" class="font-semibold mb-3">Seleccione una mesa</h2>
        <div id="productsList" class="space-y-2"></div>
    </div>
    <div class="rounded-xl border border-zinc-800 bg-zinc-950 p-4 md:col-span-2">
        <h2 class="font-semibold mb-3">Crear producto</h2>
        <form id="newProductForm" class="grid gap-2 md:grid-cols-4">
            <input id="productName" class="rounded border border-zinc-700 bg-zinc-900 px-3 py-2" placeholder="Producto" required>
            <input id="productQty" type="number" min="1" value="1" class="rounded border border-zinc-700 bg-zinc-900 px-3 py-2" required>
            <input id="productNotes" class="rounded border border-zinc-700 bg-zinc-900 px-3 py-2" placeholder="Notas">
            <button class="rounded bg-zinc-200 text-zinc-900 px-3 py-2">Agregar</button>
        </form>
    </div>
</div>
@endsection
@section('scripts')
<script>
let state = { selectedComandaId: null, comandas: @json($comandas) };
const tablesEl = document.getElementById('tables');
const productsEl = document.getElementById('productsList');
const titleEl = document.getElementById('selectedTitle');
function render() {
 tablesEl.innerHTML = state.comandas.map(c => `<button class="text-left rounded border px-3 py-2 ${state.selectedComandaId===c.id?'border-zinc-200 bg-zinc-800':'border-zinc-700 bg-zinc-900'}" onclick="selectComanda(${c.id})">Mesa ${c.mesa_numero}<br><small>${c.productos.length} productos</small></button>`).join('');
 const comanda = state.comandas.find(c=>c.id===state.selectedComandaId);
 if(!comanda){titleEl.textContent='Seleccione una mesa'; productsEl.innerHTML=''; return;}
 titleEl.textContent = `Productos de Mesa ${comanda.mesa_numero}`;
 productsEl.innerHTML = comanda.productos.map(p=>`<div class="flex gap-2 items-center"><strong>${p.nombre}</strong><input type="number" min="1" value="${p.cantidad}" onchange="updateProducto(${p.id},{cantidad:this.value})" class="w-16 rounded border border-zinc-700 bg-zinc-900 px-2 py-1"><input value="${p.notas??''}" onchange="updateProducto(${p.id},{notas:this.value})" class="rounded border border-zinc-700 bg-zinc-900 px-2 py-1"><button onclick="deleteProducto(${p.id})">Quitar</button></div>`).join('');
}
window.selectComanda=(id)=>{state.selectedComandaId=id;render();}
async function refresh(){ const r=await fetch('/comandas/data'); state.comandas=await r.json(); render(); }
window.updateProducto=async(id,data)=>{await fetch(`/productos/${id}`,{method:'PUT',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify(data)}); refresh();}
window.deleteProducto=async(id)=>{await fetch(`/productos/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}); refresh();}
document.getElementById('newProductForm').onsubmit=async(e)=>{e.preventDefault(); if(!state.selectedComandaId){alert('Seleccione una mesa');return;} await fetch('/productos',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({comanda_id:state.selectedComandaId,nombre:productName.value,cantidad:productQty.value,notas:productNotes.value})}); e.target.reset(); productQty.value=1; refresh();};
render();
</script>
@endsection
