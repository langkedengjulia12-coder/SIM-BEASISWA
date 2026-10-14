@extends('layouts.app')

@section('title', 'Dashboard Eksekutif')
@section('page-title', 'Dashboard Eksekutif')
@section('page-subtitle', 'Ringkasan kinerja program beasiswa UKRI - T.A. 2026/2027 Genap')

@section('content')
@php
    $badge = [
        'Verifikasi' => 'bg-amber-100 text-amber-800',
        'Wawancara'  => 'bg-sky-100 text-sky-800',
        'Lolos'      => 'bg-emerald-100 text-emerald-800',
        'Ditolak'    => 'bg-rose-100 text-rose-800',
    ];
    $alertStyle = [
        'warning' => 'border-amber-300 bg-amber-50 text-amber-800',
        'danger'  => 'border-rose-300 bg-rose-50 text-rose-800',
        'info'    => 'border-sky-300 bg-sky-50 text-sky-800',
    ];
@endphp

{{-- KARTU STATISTIK --}}
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ($stats as $stat)
        <div class="rounded-xl border border-slate-200 border-t-4 border-t-red-600 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
            <p class="mt-2 text-3xl font-bold text-red-700">{{ $stat['value'] }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ $stat['note'] }}</p>
        </div>
    @endforeach
</div>

<div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
    {{-- PENDAFTAR TERBARU --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm xl:col-span-2">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h2 class="font-semibold text-slate-800">Pendaftar Terbaru</h2>
            <a href="{{ route('beasiswa.applications') }}" class="text-sm font-medium text-red-700 hover:text-red-800 hover:underline">Lihat semua</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Nama</th>
                        <th class="px-5 py-3">Prodi</th>
                        <th class="px-5 py-3">Skema</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($recentApplications as $app)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <p class="font-medium text-slate-800">{{ $app['nama'] }}</p>
                                <p class="text-xs text-slate-500">{{ $app['nim'] }}</p>
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $app['prodi'] }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $app['skema'] }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge[$app['status']] ?? 'bg-slate-100 text-slate-700' }}">{{ $app['status'] }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- KOLOM KANAN --}}
    <div class="space-y-6">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-semibold text-slate-800">Sumber Pendanaan</h2>
            <div class="mt-4 space-y-4">
                @foreach ($fundBySource as $src)
                    <div>
                        <div class="mb-1 flex justify-between text-sm">
                            <span class="text-slate-600">{{ $src['sumber'] }}</span>
                            <span class="font-semibold text-slate-800">{{ $src['persen'] }}%</span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-100">
                            <div class="h-2 rounded-full bg-red-600" style="width: {{ $src['persen'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-semibold text-slate-800">Perlu Perhatian</h2>
            <ul class="mt-3 space-y-2">
                @foreach ($alerts as $alert)
                    <li class="rounded-lg border-l-4 px-3 py-2 text-sm {{ $alertStyle[$alert['level']] ?? '' }}">{{ $alert['text'] }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
