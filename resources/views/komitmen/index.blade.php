@extends('layouts.app')
@section('title', 'Komitmen Dana')
@section('page-title', 'Pantau Komitmen Dana')
@section('page-subtitle', 'Komitmen dan realisasi penerimaan dana dari mitra (P-04)')

@section('content')
@php $staf = auth()->user()->isStaf(); @endphp

<div class="mb-6 grid gap-4 sm:grid-cols-3">
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Total Komitmen</p><p class="mt-1 text-xl font-bold text-slate-800">{{ \App\Support\Kode::rupiah($total['komitmen']) }}</p></div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Dana Diterima</p><p class="mt-1 text-xl font-bold text-emerald-700">{{ \App\Support\Kode::rupiah($total['diterima']) }}</p></div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Belum Diterima</p><p class="mt-1 text-xl font-bold text-red-700">{{ \App\Support\Kode::rupiah($total['komitmen'] - $total['diterima']) }}</p></div>
</div>

@if ($staf)
<details class="mb-6 rounded-xl border border-slate-200 bg-white shadow-sm" @if ($errors->any()) open @endif>
    <summary class="cursor-pointer px-5 py-3 text-sm font-semibold text-red-700">+ Tambah Komitmen Dana</summary>
    <form method="POST" action="{{ route('komitmen.store') }}" class="grid gap-4 border-t border-slate-100 p-5 sm:grid-cols-2 lg:grid-cols-3">
        @csrf
        <x-select-field label="Kontrak (aktif)" name="id_kontrak" required>
            <option value="">- pilih kontrak -</option>
            @foreach ($kontrak as $k)<option value="{{ $k->id_kontrak }}" @selected(old('id_kontrak') == $k->id_kontrak)>{{ $k->no_dokumen }} - {{ $k->mitra->nama_mitra }}</option>@endforeach
        </x-select-field>
        <x-select-field label="Skema yang didanai" name="id_skema">
            <option value="">- belum ditentukan -</option>
            @foreach ($skema as $s)<option value="{{ $s->id_skema }}" @selected(old('id_skema') == $s->id_skema)>{{ $s->nama_skema }}</option>@endforeach
        </x-select-field>
        <x-field label="Tanggal Komitmen" name="tgl_komitmen" type="date" :value="now()->toDateString()" :keep="true" required />
        <x-field label="Nominal Komitmen (Rp)" name="nominal_komitmen" type="number" min="1" step="1" required />
        <x-field label="Keterangan" name="keterangan" class="sm:col-span-2" />
        <div class="sm:col-span-2 lg:col-span-3"><button class="rounded-lg bg-red-700 px-5 py-2 text-sm font-semibold text-white hover:bg-red-800">Simpan</button></div>
    </form>
</details>
@endif

<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50"><tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
            <th class="px-3 py-2">ID</th><th class="px-3 py-2">Mitra / Kontrak</th><th class="px-3 py-2">Skema</th><th class="px-3 py-2 text-right">Komitmen</th><th class="px-3 py-2 text-right">Diterima</th><th class="px-3 py-2">Progres</th>@if ($staf)<th class="px-3 py-2">Catat Penerimaan</th>@endif
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
        @forelse ($komitmen as $k)
            <tr>
                <td class="px-3 py-2 font-mono text-xs">{{ $k->id_komitmen }}</td>
                <td class="px-3 py-2"><span class="font-medium">{{ $k->kontrak->mitra->nama_mitra }}</span><br><span class="text-xs text-slate-500">{{ $k->kontrak->no_dokumen }}</span></td>
                <td class="px-3 py-2">{{ $k->skema->nama_skema ?? '-' }}</td>
                <td class="px-3 py-2 text-right">{{ \App\Support\Kode::rupiah($k->nominal_komitmen) }}</td>
                <td class="px-3 py-2 text-right">{{ \App\Support\Kode::rupiah($k->nominal_diterima) }}<br><span class="text-xs text-slate-400">{{ $k->tgl_terima_terakhir?->format('d-m-Y') }}</span></td>
                <td class="px-3 py-2"><div class="h-2 w-28 overflow-hidden rounded-full bg-slate-100"><div class="h-2 rounded-full bg-red-700" style="width: {{ min($k->persen_diterima, 100) }}%"></div></div><span class="text-xs text-slate-500">{{ $k->persen_diterima }}%</span></td>
                @if ($staf)
                    <td class="px-3 py-2">
                        @if ($k->nominal_diterima < $k->nominal_komitmen)
                        <form method="POST" action="{{ route('komitmen.penerimaan', $k) }}" class="flex items-center gap-1">
                            @csrf
                            <input type="number" name="jumlah" min="1" step="1" required placeholder="Rp" class="w-28 rounded-lg border-slate-300 py-1 text-xs focus:border-red-600 focus:ring-red-600">
                            <button class="rounded-lg bg-red-700 px-3 py-1 text-xs font-semibold text-white hover:bg-red-800">Catat</button>
                        </form>
                        @else <span class="text-xs font-semibold text-emerald-700">Lunas</span>@endif
                    </td>
                @endif
            </tr>
        @empty
            <tr><td colspan="7" class="px-3 py-6 text-center text-slate-400">Belum ada komitmen dana.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
