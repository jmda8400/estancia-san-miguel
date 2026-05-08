@extends('layouts.internal')
@section('title', 'Stock')
@section('internal_title', 'Vista de stock')

@section('internal_content')
<section class="stock-view mx-auto w-full max-w-[78rem] space-y-4">
<div class="stock-tabs flex gap-2">
    <button id="tabStock" class="app-btn app-stock-tab app-btn-active" onclick="switchTab('stock')">Stock</button>
    <button id="tabHistorial" class="app-btn app-stock-tab" onclick="switchTab('historial')">Historial</button>
</div>

<div id="stockTab" class="app-card app-stock-card">
    <div class="app-stock-table-wrap">
    <table class="w-full text-sm app-list-table">
        <thead><tr><th class="text-left">Producto</th><th class="text-left">Cantidad</th><th class="text-left">Precio</th><th></th></tr></thead>
        <tbody class="text-zinc-900" id="stockBody"></tbody>
    </table>
    </div>
    <button class="mt-4 app-btn app-stock-add-btn" onclick="addRow()">+ Agregar fila</button>
</div>

<div id="historialTab" class="hidden app-card app-stock-card">
    <h2 class="font-semibold mb-3">Historial de cambios de stock</h2>
    <div id="stockHistoryList" class="space-y-2"></div>
</div>
</section>

@endsection
@section('scripts')
<script>
let stockItems = @json($stockItems);

function rowTemplate(item){
    return `<tr><td>${item.id ? item.producto : `<input class='app-input w-full' placeholder='Producto' id='p_${item.tmpId}'>`}</td><td>${item.id ? `<input data-field='cantidad' type='number' min='0' value='${item.cantidad}' class='app-input app-stock-input' onchange='updateStock(${item.id}, this)'>` : `<input type='number' min='0' value='0' class='app-input app-stock-input' id='c_${item.tmpId}'>`}</td><td>${item.id ? `<input data-field='precio' type='number' min='0' step='0.01' value='${item.precio ?? 0}' class='app-input app-stock-input' onchange='updateStock(${item.id}, this)'>` : `<input type='number' min='0' step='0.01' value='0' class='app-input app-stock-input' id='pr_${item.tmpId}'>`}</td><td class='text-right'>${item.id ? `<button class='app-btn app-stock-row-btn' onclick='removeStock(${item.id})'>Quitar</button>` : `<button class='app-btn app-stock-row-btn' onclick='saveRow(${item.tmpId})'>Guardar</button>`}</td></tr>`;
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
    const precio = document.getElementById(`pr_${tmpId}`).value;
    await fetch('/stock',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({producto,cantidad,precio})});
    await refreshStock();
    await refreshHistory();
};

window.removeStock = async (id) => {
    await fetch(`/stock/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}});
    await refreshStock();
    await refreshHistory();
};

window.updateStock=async(id,input)=>{
    const row = input.closest('tr');
    const cantidad = row.querySelector("input[data-field='cantidad']").value;
    const precio = row.querySelector("input[data-field='precio']").value;
    await fetch(`/stock/${id}`,{method:'PUT',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({cantidad,precio})});
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
    document.getElementById('stockHistoryList').innerHTML = data.map(h => `<div class='rounded border border-amber-200 bg-amber-50 p-3'><strong>${labels[h.accion]}</strong> · ${h.producto} · ${h.cantidad}<div class='text-sm text-amber-950'>${new Date(h.created_at).toLocaleString()}</div></div>`).join('') || '<p class="text-amber-900">No hay movimientos.</p>';
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
