@extends('layout')
@section('title', 'Stock')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Vista de stock</h1>

<div class="mb-4 flex gap-2">
    <button id="tabStock" class="rounded px-4 py-2 bg-zinc-100 text-zinc-900" onclick="switchTab('stock')">Stock</button>
    <button id="tabHistorial" class="rounded px-4 py-2 bg-zinc-700 text-zinc-100" onclick="switchTab('historial')">Historial</button>
</div>

<div id="stockTab" class="rounded-xl border border-zinc-500 bg-zinc-700 p-6">
    <table class="w-full mt-1 text-sm">
        <thead class="text-zinc-100"><tr class="border-b border-zinc-500"><th class="text-left py-2">Producto</th><th class="text-left py-2">Cantidad</th><th class="text-left py-2">Unidad</th><th></th></tr></thead>
        <tbody class="text-zinc-100" id="stockBody"></tbody>
    </table>
    <button class="mt-4 rounded bg-zinc-100 text-zinc-900 px-3 py-2" onclick="addRow()">+ Agregar fila</button>
</div>

<div id="historialTab" class="hidden rounded-xl border border-zinc-500 bg-zinc-700 p-6">
    <h2 class="font-semibold mb-3">Historial de cambios de stock</h2>
    <div id="stockHistoryList" class="space-y-2"></div>
</div>
@endsection
@section('scripts')
<script>
let stockItems = @json($stockItems);

function rowTemplate(item){
    return `<tr class="border-b border-zinc-600"><td class="py-2">${item.id ? item.producto : `<input class='w-full rounded border border-zinc-500 bg-zinc-600 px-2 py-1' placeholder='Producto' id='p_${item.tmpId}'>`}</td><td>${item.id ? `<input type='number' min='0' value='${item.cantidad}' class='w-24 rounded border border-zinc-500 bg-zinc-600 px-2 py-1' onchange='updateStock(${item.id}, this.value)'>` : `<input type='number' min='0' value='0' class='w-24 rounded border border-zinc-500 bg-zinc-600 px-2 py-1' id='c_${item.tmpId}'>`}</td><td>${item.id ? item.unidad : `<input class='w-full rounded border border-zinc-500 bg-zinc-600 px-2 py-1' placeholder='Unidad' id='u_${item.tmpId}'>`}</td><td class='text-right'>${item.id ? `<button class='rounded px-2 py-1 bg-zinc-600 hover:bg-zinc-500' onclick='removeStock(${item.id})'>Quitar</button>` : `<button class='rounded px-2 py-1 bg-green-600 hover:bg-green-500' onclick='saveRow(${item.tmpId})'>Guardar</button>`}</td></tr>`;
}

function renderStock(){
    document.getElementById('stockBody').innerHTML = stockItems.map(rowTemplate).join('');
}

window.addRow = () => {
    stockItems.push({ tmpId: Date.now() + Math.floor(Math.random() * 1000) });
    renderStock();
};

window.saveRow = async (tmpId) => {
    const producto = document.getElementById(`p_${tmpId}`).value;
    const cantidad = document.getElementById(`c_${tmpId}`).value;
    const unidad = document.getElementById(`u_${tmpId}`).value;
    await fetch('/stock',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({producto,cantidad,unidad})});
    await refreshStock();
    await refreshHistory();
};

window.removeStock = async (id) => {
    await fetch(`/stock/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}});
    await refreshStock();
    await refreshHistory();
};

window.updateStock=async(id,cantidad)=>{
    await fetch(`/stock/${id}`,{method:'PUT',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({cantidad})});
    await refreshStock();
    await refreshHistory();
}

async function refreshStock(){
    const response = await fetch('/stock/data');
    stockItems = await response.json();
    renderStock();
}

async function refreshHistory(){
    const response = await fetch('/stock/historial/data');
    const data = await response.json();
    const labels = { suma: 'Suma', resta: 'Resta', agregado: 'Agregado', quitado: 'Quitado' };
    document.getElementById('stockHistoryList').innerHTML = data.map(h => `<div class='rounded border border-zinc-500 bg-zinc-600 p-3'><strong>${labels[h.accion]}</strong> · ${h.producto} · ${h.cantidad} ${h.unidad}<div class='text-sm text-zinc-300'>${new Date(h.created_at).toLocaleString()}</div></div>`).join('') || '<p class="text-zinc-300">No hay movimientos.</p>';
}

window.switchTab = (tab) => {
    document.getElementById('stockTab').classList.toggle('hidden', tab !== 'stock');
    document.getElementById('historialTab').classList.toggle('hidden', tab !== 'historial');
    document.getElementById('tabStock').className = `rounded px-4 py-2 ${tab === 'stock' ? 'bg-zinc-100 text-zinc-900' : 'bg-zinc-700 text-zinc-100'}`;
    document.getElementById('tabHistorial').className = `rounded px-4 py-2 ${tab === 'historial' ? 'bg-zinc-100 text-zinc-900' : 'bg-zinc-700 text-zinc-100'}`;
    if (tab === 'historial') refreshHistory();
};

renderStock();
refreshHistory();
</script>
@endsection
