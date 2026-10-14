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

<div class="mb-4 flex justify-end">
    <button type="button" class="inline-flex items-center rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">
        + Tambah Skema
    </button>
</div>

<div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
    @foreach ($schemes as $s)
        @php
            $persen = $s['kuota'] > 0 ? round($s['terisi'] / $s['kuota'] * 100) : 0;
            $bar = $persen >= 100 ? 'bg-red-800' : 'bg-red-600';
        @endphp
        <div class="flex flex-col rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="font-mono text-xs font-semibold text-slate-500">{{ $s['kode'] }}</p>
                    <h3 class="mt-1 text-lg font-bold text-red-700">{{ $s['nama'] }}</h3>
                </div>
                <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge[$s['status']] ?? 'bg-slate-100 text-slate-700' }}">{{ $s['status'] }}</span>
            </div>

            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Sumber Dana</dt><dd class="text-right font-medium text-slate-800">{{ $s['mitra'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Cakupan</dt><dd class="text-right font-medium text-slate-800">{{ $s['cakupan'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Nominal / Semester</dt><dd class="text-right font-semibold text-slate-800">Rp {{ number_format($s['nominal'], 0, ',', '.') }}</dd></div>
            </dl>

            <div class="mt-4">
                <div class="mb-1 flex justify-between text-xs text-slate-500">
                    <span>Kuota terisi</span>
                    <span class="font-semibold text-slate-700">{{ $s['terisi'] }} / {{ $s['kuota'] }} ({{ $persen }}%)</span>
                </div>
                <div class="h-2 rounded-full bg-slate-100">
                    <div class="h-2 rounded-full {{ $bar }}" style="width: {{ min($persen, 100) }}%"></div>
                </div>
            </div>

            <div class="mt-5 flex gap-2 border-t border-slate-100 pt-4">
                <button type="button" class="flex-1 rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Detail</button>
                <button type="button" class="flex-1 rounded-lg bg-red-700 px-3 py-1.5 text-sm font-semibold text-white hover:bg-red-800">Ubah</button>
            </div>
        </div>
    @endforeach
</div>
@endsection
