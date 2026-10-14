@extends('layouts.app')

@section('title', 'Manajemen Kemitraan & MoU')
@section('page-title', 'Manajemen Kemitraan & MoU')
@section('page-subtitle', 'Daftar mitra penyandang dana beserta status Memorandum of Understanding')

@section('content')
@php
    $badge = [
        'Aktif'           => 'bg-emerald-100 text-emerald-800',
        'Segera Berakhir' => 'bg-amber-100 text-amber-800',
        'Kedaluwarsa'     => 'bg-rose-100 text-rose-800',
    ];
    $totalDana = collect($partners)->sum('dana');
    $aktif = collect($partners)->where('status', 'Aktif')->count();
@endphp

{{-- Kartu statistik: 3 kolom sejajar --}}
<div class="grid grid-cols-3 gap-4 lg:gap-6">
    <div class="min-w-0 rounded-xl border border-slate-200 border-t-4 border-t-red-600 bg-white p-4 shadow-sm lg:p-6">
        <p class="text-xs font-medium uppercase tracking-wide text-slate-500 lg:text-sm lg:normal-case lg:tracking-normal">Total Mitra</p>
        <p class="mt-2 truncate text-2xl font-bold text-red-700 lg:text-3xl">{{ count($partners) }}</p>
        <p class="mt-1 text-xs text-slate-400">Mitra penyandang dana terdaftar</p>
    </div>
    <div class="min-w-0 rounded-xl border border-slate-200 border-t-4 border-t-red-600 bg-white p-4 shadow-sm lg:p-6">
        <p class="text-xs font-medium uppercase tracking-wide text-slate-500 lg:text-sm lg:normal-case lg:tracking-normal">Total Komitmen Dana</p>
        <p class="mt-2 truncate text-2xl font-bold text-red-700 lg:text-3xl">Rp {{ number_format($totalDana, 0, ',', '.') }}</p>
        <p class="mt-1 text-xs text-slate-400">Akumulasi seluruh MoU</p>
    </div>
    <div class="min-w-0 rounded-xl border border-slate-200 border-t-4 border-t-red-600 bg-white p-4 shadow-sm lg:p-6">
        <p class="text-xs font-medium uppercase tracking-wide text-slate-500 lg:text-sm lg:normal-case lg:tracking-normal">MoU Aktif</p>
        <p class="mt-2 truncate text-2xl font-bold text-red-700 lg:text-3xl">{{ $aktif }}</p>
        <p class="mt-1 text-xs text-slate-400">dari {{ count($partners) }} MoU tercatat</p>
    </div>
</div>

{{-- Tabel data mitra --}}
<div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-col gap-3 border-b border-slate-200 bg-white px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-base font-semibold text-slate-800">Daftar Mitra & MoU</h2>
            <p class="text-xs text-slate-500">{{ count($partners) }} mitra terdaftar</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <input type="search" placeholder="Cari mitra atau nomor MoU..."
                   class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm placeholder-slate-400 focus:border-red-600 focus:ring-1 focus:ring-red-600 sm:w-72">
            <button type="button"
                    class="inline-flex items-center justify-center whitespace-nowrap rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-800">
                + Tambah Mitra
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                    <th scope="col" class="whitespace-nowrap px-6 py-3">Mitra</th>
                    <th scope="col" class="whitespace-nowrap px-6 py-3">Jenis</th>
                    <th scope="col" class="whitespace-nowrap px-6 py-3">No. MoU</th>
                    <th scope="col" class="whitespace-nowrap px-6 py-3">Masa Berlaku</th>
                    <th scope="col" class="whitespace-nowrap px-6 py-3 text-right">Komitmen Dana</th>
                    <th scope="col" class="whitespace-nowrap px-6 py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
                @foreach ($partners as $p)
                    <tr class="transition hover:bg-slate-50">
                        <td class="whitespace-nowrap px-6 py-4 font-medium text-slate-900">{{ $p['nama'] }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-slate-600">{{ $p['jenis'] }}</td>
                        <td class="whitespace-nowrap px-6 py-4 font-mono text-xs text-slate-600">{{ $p['mou'] }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-slate-600">{{ $p['berlaku'] }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-right font-semibold tabular-nums text-slate-900">Rp {{ number_format($p['dana'], 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-center">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge[$p['status']] ?? 'bg-slate-100 text-slate-700' }}">{{ $p['status'] }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
