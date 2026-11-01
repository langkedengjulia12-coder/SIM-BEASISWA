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
    $totalDana = $partners->sum('funding_amount');
    $aktif = $partners->where('status', 'active')->count();
@endphp

@if (session('success'))
    <div class="mb-4 rounded-lg bg-emerald-100 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
@endif

<form id="tambah-mitra" method="POST" action="{{ route('admin.partners.store') }}" class="mb-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    @csrf
    <h2 class="text-base font-semibold text-slate-800">Tambah Mitra & MoU</h2>
    <div class="mt-4 grid gap-3 md:grid-cols-3 lg:grid-cols-4">
        <input name="name" required placeholder="Nama mitra" class="rounded-lg border-slate-300 text-sm">
        <input name="type" required placeholder="Jenis mitra" class="rounded-lg border-slate-300 text-sm">
        <input name="mou_number" required placeholder="Nomor MoU" class="rounded-lg border-slate-300 text-sm">
        <input name="funding_amount" required type="number" min="0" step="0.01" placeholder="Komitmen dana" class="rounded-lg border-slate-300 text-sm">
        <label class="text-xs text-slate-500">Mulai berlaku<input name="mou_start" required type="date" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></label>
        <label class="text-xs text-slate-500">Berakhir berlaku<input name="mou_end" required type="date" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></label>
        <select name="status" required class="rounded-lg border-slate-300 text-sm"><option value="active">Aktif</option><option value="inactive">Tidak Aktif</option><option value="expired">Kedaluwarsa</option></select>
        <button class="rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">Simpan Mitra</button>
    </div>
</form>

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
            <a href="#tambah-mitra" class="inline-flex items-center justify-center whitespace-nowrap rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-800">+ Tambah Mitra</a>
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
                        <td class="whitespace-nowrap px-6 py-4 font-medium text-slate-900">{{ $p->name }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-slate-600">{{ $p->type }}</td>
                        <td class="whitespace-nowrap px-6 py-4 font-mono text-xs text-slate-600">{{ $p->mou_number }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-slate-600">{{ $p->mou_start->format('d M Y') }} - {{ $p->mou_end->format('d M Y') }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-right font-semibold tabular-nums text-slate-900">Rp {{ number_format($p->funding_amount, 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-center">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge[$p->status === 'active' ? 'Aktif' : ($p->status === 'expired' ? 'Kedaluwarsa' : 'Tidak Aktif')] ?? 'bg-slate-100 text-slate-700' }}">{{ $p->status === 'active' ? 'Aktif' : ($p->status === 'expired' ? 'Kedaluwarsa' : 'Tidak Aktif') }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6 space-y-3">
    @foreach ($partners as $p)
        <details class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <summary class="cursor-pointer text-sm font-semibold text-slate-700">Ubah {{ $p->name }} atau hapus data</summary>
            <div class="mt-4 flex flex-col gap-4 lg:flex-row">
                <form method="POST" action="{{ route('admin.partners.update', $p) }}" class="grid flex-1 gap-3 md:grid-cols-3">
                    @csrf @method('PUT')
                    <input name="name" required value="{{ $p->name }}" class="rounded-lg border-slate-300 text-sm">
                    <input name="type" required value="{{ $p->type }}" class="rounded-lg border-slate-300 text-sm">
                    <input name="mou_number" required value="{{ $p->mou_number }}" class="rounded-lg border-slate-300 text-sm">
                    <input name="funding_amount" required type="number" min="0" step="0.01" value="{{ $p->funding_amount }}" class="rounded-lg border-slate-300 text-sm">
                    <input name="mou_start" required type="date" value="{{ $p->mou_start->format('Y-m-d') }}" class="rounded-lg border-slate-300 text-sm">
                    <input name="mou_end" required type="date" value="{{ $p->mou_end->format('Y-m-d') }}" class="rounded-lg border-slate-300 text-sm">
                    <select name="status" required class="rounded-lg border-slate-300 text-sm"><option value="active" @selected($p->status === 'active')>Aktif</option><option value="inactive" @selected($p->status === 'inactive')>Tidak Aktif</option><option value="expired" @selected($p->status === 'expired')>Kedaluwarsa</option></select>
                    <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white">Simpan Perubahan</button>
                </form>
                <form method="POST" action="{{ route('admin.partners.destroy', $p) }}" onsubmit="return confirm('Hapus mitra ini?')">
                    @csrf @method('DELETE')
                    <button class="rounded-lg border border-rose-300 px-4 py-2 text-sm font-semibold text-rose-700">Hapus</button>
                </form>
            </div>
        </details>
    @endforeach
</div>
@endsection
