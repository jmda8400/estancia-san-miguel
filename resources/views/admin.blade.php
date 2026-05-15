@extends('layouts.internal')
@section('title', 'Administración')
@section('internal_title', 'Vista de administración')
@section('internal_content')
<div class="app-card">
    <div class="mb-4 app-internal-tabs">
        <button id="sectionConfig" class="app-internal-tab-btn app-btn-active">Configuración</button>
        <button id="sectionDb" class="app-internal-tab-btn">Base de Datos</button>
    </div>

    <section id="configSection">
        <div class="mb-4 grid gap-3 md:grid-cols-4">
            <div>
                <label for="startDate" class="font-medium block mb-1">Desde</label>
                <input id="startDate" type="date" class="app-input w-full">
            </div>
            <div>
                <label for="endDate" class="font-medium block mb-1">Hasta</label>
                <input id="endDate" type="date" class="app-input w-full">
            </div>
            <div class="md:col-span-2 flex items-end gap-2 flex-wrap">
                <button class="app-btn app-btn-pill" data-range="7">Últimos 7 días</button>
                <button class="app-btn app-btn-active app-btn-pill" data-range="30">Últimos 30 días</button>
                <button class="app-btn app-btn-pill" data-range="90">Últimos 90 días</button>
                <button id="applyRange" class="app-btn app-btn-pill">Aplicar rango</button>
            </div>
        </div>
        <section class="app-chart-wrap mb-4">
            <h2 class="font-semibold mb-3">Fluctuación de cantidad por producto</h2>
            <div id="adminCharts"></div>
        </section>
        <div class="app-chart-wrap">
            <h2 class="font-semibold mb-3">Configuración del ticket</h2>
            <div class="grid md:grid-cols-2 gap-3">
                <input id="telefonoLocal" class="app-input w-full" placeholder="Número de teléfono">
                <input id="adminAlias" class="app-input w-full md:col-span-2" placeholder="Alias de administración (para QR en comprobantes)">
                <button id="savePhone" class="app-btn app-btn-pill md:col-span-2">Guardar configuración de ticket</button>
            </div>
            <p class="text-xs mt-2 text-neutral-700">Se imprimirá en: <code>storage/app/public/comprobantes</code></p>
        </div>
    </section>

    <section id="dbSection" class="hidden">
        <div class="app-chart-wrap">
            <h2 class="font-semibold mb-3">Base de Datos</h2>
            <div class="rounded-lg border border-neutral-300 bg-white/70 p-3 shadow-sm">
                <div class="flex flex-wrap gap-2 mb-3">
                    <button id="reloadTables" class="app-btn app-btn-pill">Actualizar tablas</button>
                    <button id="downloadDb" class="app-btn app-btn-pill">Descargar base completa</button>
                    <label class="app-btn app-btn-pill cursor-pointer">Cargar base completa
                        <input id="uploadDb" type="file" accept="application/json" class="hidden">
                    </label>
                    <button id="deleteDb" class="app-btn app-btn-pill bg-red-700 text-white border-red-700 hover:bg-red-800">Eliminar toda la base</button>
                </div>
                <div class="mb-3">
                    <input id="tableSearch" type="search" class="app-input w-full md:w-80" placeholder="Buscar tabla...">
                </div>
                <div id="dbTables" class="rounded-lg border border-neutral-200 bg-white max-h-[60vh] overflow-y-auto"></div>
            </div>
        </div>

        <div id="uploadTableModal" class="fixed inset-0 hidden items-center justify-center z-50 bg-black/40 p-4">
            <div class="w-full max-w-md rounded-xl bg-white p-4 shadow-xl">
                <h3 class="font-semibold text-lg">Cargar tabla</h3>
                <p id="uploadTableText" class="text-sm text-neutral-700 mt-1"></p>
                <input id="uploadTableInput" type="file" accept="application/json" class="app-input w-full mt-3">
                <div class="flex justify-end gap-2 mt-4">
                    <button class="app-btn app-btn-pill" onclick="closeModal('uploadTableModal')">Cancelar</button>
                    <button id="confirmUploadTable" class="app-btn app-btn-pill">Confirmar carga</button>
                </div>
            </div>
        </div>

        <div id="deleteTableModal" class="fixed inset-0 hidden items-center justify-center z-50 bg-black/40 p-4">
            <div class="w-full max-w-md rounded-xl bg-white p-4 shadow-xl">
                <h3 class="font-semibold text-lg">Confirmar eliminación</h3>
                <p id="deleteTableText" class="text-sm text-neutral-700 mt-1"></p>
                <p class="text-sm text-red-700 mt-2">Esta acción no se puede deshacer.</p>
                <div class="flex justify-end gap-2 mt-4">
                    <button class="app-btn app-btn-pill" onclick="closeModal('deleteTableModal')">Cancelar</button>
                    <button id="confirmDeleteTable" class="app-btn app-btn-pill bg-red-700 text-white border-red-700 hover:bg-red-800">Eliminar</button>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
@section('scripts')
<script>
const dbState = { all: [], filtered: [], selectedTable: null };
function switchSection(section) {
    const config = document.getElementById('configSection');
    const db = document.getElementById('dbSection');
    const btnConfig = document.getElementById('sectionConfig');
    const btnDb = document.getElementById('sectionDb');
    if (section === 'db') {
        config.classList.add('hidden');
        db.classList.remove('hidden');
        btnConfig.classList.remove('app-btn-active');
        btnDb.classList.add('app-btn-active');
        loadTables();
    } else {
        db.classList.add('hidden');
        config.classList.remove('hidden');
        btnDb.classList.remove('app-btn-active');
        btnConfig.classList.add('app-btn-active');
    }
}
async function loadTables() {
    const container = document.getElementById('dbTables');
    container.innerHTML = '<p class="p-3">Cargando tablas...</p>';
    const response = await fetch('/admin/base-datos/tablas');
    if (!response.ok) {
        container.innerHTML = '<p class="p-3">No se pudieron cargar las tablas.</p>';
        return;
    }
    const tables = await response.json();
    const now = new Date();
    dbState.all = tables.map((table) => ({
        table,
        records: 0,
        updatedAt: now,
    }));
    applyTableFilter();
    await Promise.all(dbState.all.map(async (item) => {
        try {
            const detail = await fetch(`/admin/base-datos/tabla/${encodeURIComponent(item.table)}/descargar`);
            if (!detail.ok) return;
            const payload = await detail.json();
            const rows = Array.isArray(payload.rows) ? payload.rows : [];
            item.records = rows.length;
            const last = rows.map((row) => row.updated_at || row.created_at).filter(Boolean).sort().at(-1);
            item.updatedAt = last ? new Date(last) : null;
        } catch (_) {}
    }));
    applyTableFilter();
}

function formatDate(date) {
    if (!date || Number.isNaN(date.getTime?.())) return 'Sin datos';
    return new Intl.DateTimeFormat('es-AR', { dateStyle: 'short', timeStyle: 'short' }).format(date);
}

function applyTableFilter() {
    const query = document.getElementById('tableSearch').value.trim().toLowerCase();
    dbState.filtered = dbState.all.filter(({ table }) => table.toLowerCase().includes(query));
    renderTables();
}

function renderTables() {
    const container = document.getElementById('dbTables');
    if (!dbState.filtered.length) {
        container.innerHTML = '<p class="p-3 text-neutral-700">No hay tablas para mostrar.</p>';
        return;
    }
    container.innerHTML = `
        <div class="hidden md:grid grid-cols-[2fr_1fr_1.4fr_auto] px-3 py-2 border-b border-neutral-200 bg-neutral-50 text-xs font-semibold uppercase tracking-wide text-neutral-600">
            <div>Tabla</div><div>Registros</div><div>Última actualización</div><div class="text-right">Acciones</div>
        </div>
        ${dbState.filtered.map(({ table, records, updatedAt }) => `
            <article class="border-b last:border-b-0 border-neutral-200 p-3">
                <div class="hidden md:grid md:grid-cols-[2fr_1fr_1.4fr_auto] md:items-center gap-2">
                    <div class="font-medium">${table}</div>
                    <div>${records} registros</div>
                    <div class="text-sm text-neutral-700">${formatDate(updatedAt)}</div>
                    <div class="flex justify-end gap-1">
                        <button class="app-btn app-btn-pill text-xs px-3 py-1" onclick="downloadTable('${table}')">Descargar</button>
                        <button class="app-btn app-btn-pill text-xs px-3 py-1 border-amber-600 text-amber-700" onclick="openUploadTableModal('${table}')">Cargar</button>
                        <button class="app-btn app-btn-pill text-xs px-3 py-1 bg-red-700 text-white border-red-700 hover:bg-red-800" onclick="openDeleteTableModal('${table}')">Eliminar</button>
                    </div>
                </div>
                <div class="md:hidden">
                    <div class="flex items-center justify-between gap-2">
                        <div><div class="font-medium">${table}</div><div class="text-xs text-neutral-600">${records} registros · ${formatDate(updatedAt)}</div></div>
                        <div class="flex gap-1">
                            <button class="app-btn app-btn-pill text-xs px-2 py-1" onclick="downloadTable('${table}')">⬇</button>
                            <button class="app-btn app-btn-pill text-xs px-2 py-1 border-amber-600 text-amber-700" onclick="openUploadTableModal('${table}')">⬆</button>
                            <button class="app-btn app-btn-pill text-xs px-2 py-1 bg-red-700 text-white border-red-700 hover:bg-red-800" onclick="openDeleteTableModal('${table}')">🗑</button>
                        </div>
                    </div>
                </div>
            </article>
        `).join('')}
    `;
}

function openModal(id) { document.getElementById(id).classList.remove('hidden'); document.getElementById(id).classList.add('flex'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); document.getElementById(id).classList.remove('flex'); }

function openUploadTableModal(table) {
    dbState.selectedTable = table;
    document.getElementById('uploadTableText').textContent = `Seleccioná un archivo para cargar en la tabla "${table}".`;
    document.getElementById('uploadTableInput').value = '';
    openModal('uploadTableModal');
}

function openDeleteTableModal(table) {
    dbState.selectedTable = table;
    document.getElementById('deleteTableText').textContent = `Se eliminarán todos los registros de "${table}".`;
    openModal('deleteTableModal');
}

async function downloadTable(table) {
    const response = await fetch(`/admin/base-datos/tabla/${encodeURIComponent(table)}/descargar`);
    if (!response.ok) return alert('No se pudo descargar la tabla.');
    const data = await response.json();
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `${table}.json`;
    a.click();
}
async function uploadTable(table, file) {
    if (!file) return;
    const text = await file.text();
    const parsed = JSON.parse(text);
    const rows = Array.isArray(parsed) ? parsed : (parsed.rows || []);
    const response = await fetch(`/admin/base-datos/tabla/${encodeURIComponent(table)}/cargar`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
        body: JSON.stringify({ rows }),
    });
    if (!response.ok) return alert('No se pudo cargar la tabla.');
    alert(`Tabla ${table} cargada.`);
    loadTables();
}
async function deleteTable(table) {
    const response = await fetch(`/admin/base-datos/tabla/${encodeURIComponent(table)}`, {
        method: 'DELETE',
        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
    });
    if (!response.ok) return alert('No se pudo eliminar la tabla.');
    alert(`Tabla ${table} vaciada.`);
}
async function downloadDb() {
    const response = await fetch('/admin/base-datos/descargar');
    if (!response.ok) return alert('No se pudo descargar la base.');
    const data = await response.json();
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'base_de_datos.json';
    a.click();
}
async function uploadDb(event) {
    const file = event.target.files?.[0];
    if (!file) return;
    const text = await file.text();
    const parsed = JSON.parse(text);
    const payload = parsed.tablas ? parsed : { tablas: parsed };
    const response = await fetch('/admin/base-datos/cargar', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
        body: JSON.stringify(payload),
    });
    if (!response.ok) return alert('No se pudo cargar la base de datos.');
    alert('Base de datos cargada.');
    loadTables();
}
async function deleteDb() {
    if (!confirm('¿Eliminar todos los registros de toda la base de datos?')) return;
    if (!confirm('Confirmación final: esta acción eliminará toda la base y no se puede deshacer.')) return;
    const response = await fetch('/admin/base-datos', {
        method: 'DELETE',
        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
    });
    if (!response.ok) return alert('No se pudo eliminar la base de datos.');
    alert('Base de datos vaciada.');
    loadTables();
}

// Existing config functions
async function renderAdminCharts() { /* unchanged below */
    const el = document.getElementById('adminCharts');
    const params = new URLSearchParams({ start: document.getElementById('startDate').value, end: document.getElementById('endDate').value });
    const response = await fetch(`/admin/graficas/data?${params.toString()}`);
    const data = await response.json();
    if (!data.series.length) { el.innerHTML = '<p class="text-neutral-700">No hay productos para mostrar en el período seleccionado.</p>'; return; }
    const width = 980, height = 380, padding = 58;
    const colors = ['#1f3b2d', '#5f5f5f', '#395b8a', '#7a4f95', '#a35d2f', '#2f7f6f'];
    const allValues = data.series.flatMap((product) => product.puntos.map((point) => Number(point.cantidad || 0)));
    const maxValue = Math.max(...allValues, 1); const minValue = Math.min(...allValues, 0); const span = Math.max(maxValue - minValue, 1);
    const steps = data.series[0].puntos.length; const stepX = steps > 1 ? (width - padding * 2) / (steps - 1) : 0;
    const toPoints = (points) => points.map((point, idx) => ({ x: padding + (idx * stepX), y: height - padding - (((point.cantidad - minValue) / span) * (height - padding * 2)), ...point }));
    const lines = data.series.map((product, idx) => ({ producto: product.producto, color: colors[idx % colors.length], points: toPoints(product.puntos), polyline: toPoints(product.puntos).map((point) => `${point.x},${point.y}`).join(' ') }));
    const yTicks = 5;
    const tickValues = Array.from({ length: yTicks + 1 }, (_, i) => minValue + ((span / yTicks) * i));
    const gridLines = tickValues.map((value) => { const y = height - padding - (((value - minValue) / span) * (height - padding * 2)); return `<g><line x1="${padding}" y1="${y}" x2="${width - padding}" y2="${y}" class="app-chart-grid-line"></line><text x="${padding - 10}" y="${y + 4}" text-anchor="end" class="app-chart-axis-text">${Math.round(value)}</text></g>`; }).join('');
    const xLabels = data.series[0].puntos.map((point, idx) => { if (idx % Math.ceil(steps / 6) !== 0 && idx !== steps - 1) return ''; const x = padding + (idx * stepX); return `<text x="${x}" y="${height - padding + 20}" text-anchor="middle" class="app-chart-axis-text">${point.fecha.slice(5)}</text>`; }).join('');
    el.innerHTML = `<article class="rounded-lg border border-neutral-300 bg-neutral-100 p-3"><div class="app-chart-scroll"><svg viewBox="0 0 ${width} ${height}" class="app-chart-svg" role="img" aria-label="Fluctuación de stock por producto en el tiempo">${gridLines}<line x1="${padding}" y1="${height - padding}" x2="${width - padding}" y2="${height - padding}" class="app-chart-axis"></line><line x1="${padding}" y1="${padding}" x2="${padding}" y2="${height - padding}" class="app-chart-axis"></line>${lines.map((line) => `<polyline points="${line.polyline}" fill="none" stroke="${line.color}" stroke-width="2.5"></polyline>`).join('')}${lines.map((line) => line.points.map((point) => `<circle cx="${point.x}" cy="${point.y}" r="3" fill="${line.color}"><title>${line.producto} · ${point.fecha}: ${point.cantidad}</title></circle>`).join('')).join('')}${xLabels}</svg></div><div class="app-chart-legend">${lines.map((line) => `<div><span class="inline-block h-2 w-6 mr-2 align-middle" style="background:${line.color}"></span>${line.producto}</div>`).join('')}</div></article>`;
}
function setRange(days) { const end = new Date(); const start = new Date(); start.setDate(end.getDate() - (days - 1)); document.getElementById('startDate').value = start.toISOString().slice(0, 10); document.getElementById('endDate').value = end.toISOString().slice(0, 10); }
async function loadPhoneConfig(){ const response = await fetch('/admin/configuracion'); const data = await response.json(); document.getElementById('telefonoLocal').value = data.telefono_local || ''; document.getElementById('adminAlias').value = data.admin_alias || ''; }

document.getElementById('sectionConfig').addEventListener('click', () => switchSection('config'));
document.getElementById('sectionDb').addEventListener('click', () => switchSection('db'));
document.getElementById('reloadTables').addEventListener('click', loadTables);
document.getElementById('downloadDb').addEventListener('click', downloadDb);
document.getElementById('uploadDb').addEventListener('change', uploadDb);
document.getElementById('deleteDb').addEventListener('click', deleteDb);
document.getElementById('tableSearch').addEventListener('input', applyTableFilter);
document.getElementById('confirmUploadTable').addEventListener('click', async () => {
    const file = document.getElementById('uploadTableInput').files?.[0];
    if (!dbState.selectedTable || !file) return alert('Seleccioná un archivo.');
    await uploadTable(dbState.selectedTable, file);
    closeModal('uploadTableModal');
});
document.getElementById('confirmDeleteTable').addEventListener('click', async () => {
    if (!dbState.selectedTable) return;
    await deleteTable(dbState.selectedTable);
    closeModal('deleteTableModal');
    loadTables();
});
document.getElementById('savePhone').addEventListener('click', async () => {
    const telefono_local = document.getElementById('telefonoLocal').value.trim();
    const admin_alias = document.getElementById('adminAlias').value.trim();
    if (!telefono_local) return alert('Ingrese un teléfono válido.');
    const response = await fetch('/admin/configuracion/telefono', { method: 'PUT', headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'}, body: JSON.stringify({ telefono_local, admin_alias }) });
    if (!response.ok) return alert('No se pudo guardar la configuración.');
    alert('Configuración guardada.');
});
setRange(30); loadPhoneConfig(); renderAdminCharts();
document.getElementById('applyRange').addEventListener('click', renderAdminCharts);
document.querySelectorAll('[data-range]').forEach((btn) => btn.addEventListener('click', () => { document.querySelectorAll('[data-range]').forEach((x) => x.classList.remove('app-btn-active')); btn.classList.add('app-btn-active'); setRange(Number(btn.dataset.range)); loadPhoneConfig(); renderAdminCharts(); }));
</script>
@endsection
