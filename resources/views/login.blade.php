@extends('layout')
@section('title', 'Login')
@section('content')
<div class="max-w-md mx-auto rounded-xl border border-zinc-800 bg-zinc-950 p-6">
    <h1 class="text-2xl font-semibold mb-2">Acceso al sistema</h1>
    @if ($errors->any())
        <div class="mb-4 rounded-md border border-red-900 bg-red-950/40 p-3 text-red-200">{{ $errors->first() }}</div>
    @endif
    <form action="/login" method="post" class="space-y-4">
        @csrf
        <label class="block text-sm">Usuario
            <input name="username" type="text" value="{{ old('username') }}" class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2">
        </label>
        <label class="block text-sm">Contraseña
            <input name="password" type="password" class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2">
        </label>
        <button type="submit" class="rounded-md bg-zinc-200 text-zinc-900 px-4 py-2 font-medium">Ingresar</button>
    </form>
</div>
@endsection
