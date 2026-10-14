@extends('layouts.app')

@section('title', 'Seleksi & Pendaftaran')
@section('page-title', 'Seleksi & Pendaftaran')
@section('page-subtitle', 'Pipeline seleksi calon penerima beasiswa T.A. 2026/2027 Genap')

@section('content')
@php
    $badge = [
        'Baru'    => 'bg-slate-200 text-slate-700',
        'Proses'  => 'bg-sky-100 text-sky-800',
        'Lolos'   => 'bg-emerald-100 text-emerald-800',
        'Ditolak' => 'bg-rose-100 text-rose-800',
    ];
    $maxPipeline = max(array_column($pipeline, 'jumlah'));
@endphp

{{-- PIPELINE --}}
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ($pipeline as $i => $step)
        <div class="rounded-xl border border-slate-200 border-t-4 border-t-red-600 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tahap {{ $i + 1 }}</p>
            <p class="mt-1 font-medium text-slate-700">{{ $step['tahap'] }}</p>
            <p class="mt-2 text-3xl font-bold text-red-700">{{ number_format($step['jumlah'], 0, ',', '.') }}</p>
            <div class="mt-3 h-1.5 rounded-full bg-slate-100">
                <div class="h-1.5 rounded-full bg-red-600" style="width: {{ round($step['jumlah'] / $maxPipeline * 100) }}%"></div>
            </div>
        </div>
    @endforeach
</div>

{{-- TABEL PENDAFTAR --}}
<div class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
        <input type="search" placeholder="Cari nama, NIM, atau no. registrasi..."
               class="w-full rounded-lg border-slate-300 text-sm focus:border-red-600 focus:ring-red-600 lg:w-72">
        <div class="flex flex-wrap gap-2">
            <select class="rounded-lg border-slate-300 py-2 text-sm focus:border-red-600 focus:ring-red-600">
                <option>Semua Skema</option>
                <option>Prestasi Akademik</option>
                <option>KIP Kuliah</option>
                <option>Mitra Industri</option>
                <option>Yatim Piatu</option>
            </select>
            <select class="rounded-lg border-slate-300 py-2 text-sm focus:border-red-600 focus:ring-red-600">
                <option>Semua Tahap</option>
                @foreach ($pipeline as $step)
                    <option>{{ $step['tahap'] }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3">No. Registrasi</th>
                    <th class="px-5 py-3">Pendaftar</th>
                    <th class="px-5 py-3">Prodi</th>
                    <th class="px-5 py-3 text-center">IPK</th>
                    <th class="px-5 py-3">Skema</th>
                    <th class="px-5 py-3">Tahap</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($applications as $a)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-mono text-xs text-slate-600">{{ $a['no'] }}</td>
                        <td class="px-5 py-3">
                            <p class="font-medium text-slate-800">{{ $a['nama'] }}</p>
                            <p class="text-xs text-slate-500">{{ $a['nim'] }}</p>
                        </td>
                        <td class="px-5 py-3 text-slate-600">{{ $a['prodi'] }}</td>
                        <td class="px-5 py-3 text-center font-semibold {{ $a['ipk'] >= 3.5 ? 'text-emerald-700' : 'text-slate-700' }}">{{ number_format($a['ipk'], 2) }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $a['skema'] }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $a['tahap'] }}</td>
                        <td class="px-5 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge[$a['status']] ?? 'bg-slate-100 text-slate-700' }}">{{ $a['status'] }}</span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <button type="button" class="rounded-md bg-red-700 px-3 py-1 text-xs font-semibold text-white hover:bg-red-800">Review</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
