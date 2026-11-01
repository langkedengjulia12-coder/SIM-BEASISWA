@extends('layouts.app')

@section('title', 'Skema Beasiswa')
@section('page-title', 'Skema Beasiswa')
@section('page-subtitle', 'Katalog skema beasiswa, kuota, dan nominal bantuan per semester')

@section('content')
@php
    $badge = [
        'Dibuka'  => 'bg-emerald-100 text-emerald-800',
        'Penuh'   => 'bg-amber-100 text-amber-800',
        'Ditutup' => 'bg-slate-200 text-slate-700',
    ];
@endphp

@if (session('success'))
    <div class="mb-4 rounded-lg bg-emerald-100 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
@endif

<form id="tambah-skema" method="POST" action="{{ route('admin.schemes.store') }}" class="mb-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    @csrf
    <h2 class="text-base font-semibold text-slate-800">Tambah Skema Beasiswa</h2>
    <div class="mt-4 grid gap-3 md:grid-cols-3 lg:grid-cols-4">
        <input name="code" required placeholder="Kode skema" class="rounded-lg border-slate-300 text-sm">
        <input name="name" required placeholder="Nama skema" class="rounded-lg border-slate-300 text-sm">
        <select name="partner_id" class="rounded-lg border-slate-300 text-sm"><option value="">Sumber dana: Internal UKRI</option>@foreach ($partners as $partner)<option value="{{ $partner->id }}">{{ $partner->name }}</option>@endforeach</select>
        <input name="category" required placeholder="Cakupan" class="rounded-lg border-slate-300 text-sm">
        <input name="funding_per_student" required type="number" min="0" step="0.01" placeholder="Nominal per semester" class="rounded-lg border-slate-300 text-sm">
        <input name="quota" required type="number" min="1" placeholder="Kuota" class="rounded-lg border-slate-300 text-sm">
        <input name="minimum_gpa" type="number" min="0" max="4" step="0.01" placeholder="Syarat IPK minimum" class="rounded-lg border-slate-300 text-sm">
        <input name="academic_year" required value="2026/2027 Genap" placeholder="Tahun akademik" class="rounded-lg border-slate-300 text-sm">
        <select name="status" required class="rounded-lg border-slate-300 text-sm"><option value="active">Dibuka</option><option value="inactive">Ditutup</option></select>
        <button class="rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">Simpan Skema</button>
    </div>
</form>

<div class="mb-4 flex justify-end"><a href="#tambah-skema" class="inline-flex items-center rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">+ Tambah Skema</a></div>

<div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
    @foreach ($schemes as $s)
        @php
            $persen = 0;
            $bar = $persen >= 100 ? 'bg-red-800' : 'bg-red-600';
        @endphp
        <div class="flex flex-col rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="font-mono text-xs font-semibold text-slate-500">{{ $s->code }}</p>
                    <h3 class="mt-1 text-lg font-bold text-red-700">{{ $s->name }}</h3>
                </div>
                <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge[$s->status === 'active' ? 'Dibuka' : 'Ditutup'] }}">{{ $s->status === 'active' ? 'Dibuka' : 'Ditutup' }}</span>
            </div>

            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Sumber Dana</dt><dd class="text-right font-medium text-slate-800">{{ $s->partner?->name ?? 'Internal UKRI' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Cakupan</dt><dd class="text-right font-medium text-slate-800">{{ $s->category }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Nominal / Semester</dt><dd class="text-right font-semibold text-slate-800">Rp {{ number_format($s->funding_per_student, 0, ',', '.') }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Syarat IPK</dt><dd class="text-right font-medium text-slate-800">{{ $s->minimum_gpa ?? 'Tidak ditentukan' }}</dd></div>
            </dl>

            <div class="mt-4">
                <div class="mb-1 flex justify-between text-xs text-slate-500">
                    <span>Kuota terisi</span>
                    <span class="font-semibold text-slate-700">0 / {{ $s->quota }} ({{ $persen }}%)</span>
                </div>
                <div class="h-2 rounded-full bg-slate-100">
                    <div class="h-2 rounded-full {{ $bar }}" style="width: {{ min($persen, 100) }}%"></div>
                </div>
            </div>

            <div class="mt-5 flex gap-2 border-t border-slate-100 pt-4">
                <a href="#edit-{{ $s->id }}" class="flex-1 rounded-lg border border-slate-300 px-3 py-1.5 text-center text-sm font-medium text-slate-700 hover:bg-slate-50">Detail/Ubah</a>
                <form method="POST" action="{{ route('admin.schemes.destroy', $s) }}" onsubmit="return confirm('Hapus skema ini?')" class="flex-1">@csrf @method('DELETE')<button class="w-full rounded-lg bg-rose-700 px-3 py-1.5 text-sm font-semibold text-white hover:bg-rose-800">Hapus</button></form>
            </div>
        </div>
    @endforeach
</div>

<div class="mt-6 space-y-3">
    @foreach ($schemes as $s)
        <details id="edit-{{ $s->id }}" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <summary class="cursor-pointer text-sm font-semibold text-slate-700">Ubah {{ $s->name }}</summary>
            <form method="POST" action="{{ route('admin.schemes.update', $s) }}" class="mt-4 grid gap-3 md:grid-cols-3">
                @csrf @method('PUT')
                <input name="code" required value="{{ $s->code }}" class="rounded-lg border-slate-300 text-sm">
                <input name="name" required value="{{ $s->name }}" class="rounded-lg border-slate-300 text-sm">
                <select name="partner_id" class="rounded-lg border-slate-300 text-sm"><option value="">Internal UKRI</option>@foreach ($partners as $partner)<option value="{{ $partner->id }}" @selected($s->partner_id === $partner->id)>{{ $partner->name }}</option>@endforeach</select>
                <input name="category" required value="{{ $s->category }}" class="rounded-lg border-slate-300 text-sm">
                <input name="funding_per_student" required type="number" min="0" step="0.01" value="{{ $s->funding_per_student }}" class="rounded-lg border-slate-300 text-sm">
                <input name="quota" required type="number" min="1" value="{{ $s->quota }}" class="rounded-lg border-slate-300 text-sm">
                <input name="minimum_gpa" type="number" min="0" max="4" step="0.01" value="{{ $s->minimum_gpa }}" class="rounded-lg border-slate-300 text-sm">
                <input name="academic_year" required value="{{ $s->academic_year }}" class="rounded-lg border-slate-300 text-sm">
                <select name="status" required class="rounded-lg border-slate-300 text-sm"><option value="active" @selected($s->status === 'active')>Dibuka</option><option value="inactive" @selected($s->status === 'inactive')>Ditutup</option></select>
                <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white">Simpan Perubahan</button>
            </form>
        </details>
    @endforeach
</div>
@endsection
