@extends('layout')
@section('title', 'Stock')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Vista de stock</h1>

<div class="mb-4 flex gap-2">
    <button id="tabStock" class="app-btn app-btn-active" onclick="switchTab('stock')">Stock</button>
    <button id="tabHistorial" class="app-btn" onclick="switchTab('historial')">Historial</button>
</div>

<div id="stockTab" class="app-card">
    <table class="w-full mt-1 text-sm">
        <thead class="text-zinc-100"><tr class="border-b border-zinc-500"><th class="text-left py-2">Producto</th><th class="text-left py-2">Cantidad</th><th class="text-left py-2">Unidad</th><th></th></tr></thead>
        <tbody class="text-zinc-100" id="stockBody"></tbody>
    </table>
    <button class="mt-4 app-btn" onclick="addRow()">+ Agregar fila</button>
</div>

<div id="historialTab" class="hidden app-card">
    <h2 class="font-semibold mb-3">Historial de cambios de stock</h2>
    <div id="stockHistoryList" class="space-y-2"></div>
</div>
@endsection
@section('scripts')
<script>
let stockItems = @json($stockItems);

function rowTemplate(item){
    return `<tr class="border-b border-zinc-600"><td class="py-2">${item.id ? item.producto : `<input class='app-input w-full' placeholder='Producto' id='p_${item.tmpId}'>`}</td><td>${item.id ? `<input type='number' min='0' value='${item.cantidad}' class='app-input w-24' onchange='updateStock(${item.id}, this.value)'>` : `<input type='number' min='0' value='0' class='app-input w-24' id='c_${item.tmpId}'>`}</td><td>${item.id ? item.unidad : `<input class='app-input w-full' placeholder='Unidad' id='u_${item.tmpId}'>`}</td><td class='text-right'>${item.id ? `<button class='app-btn' onclick='removeStock(${item.id})'>Quitar</button>` : `<button class='app-btn' onclick='saveRow(${item.tmpId})'>Guardar</button>`}</td></tr>`;
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
    document.getElementById('tabStock').className = `app-btn ${tab === 'stock' ? 'app-btn-active' : ''}`;
    document.getElementById('tabHistorial').className = `app-btn ${tab === 'historial' ? 'app-btn-active' : ''}`;
    if (tab === 'historial') refreshHistory();
};

renderStock();
refreshHistory();
</script>
@endsection
