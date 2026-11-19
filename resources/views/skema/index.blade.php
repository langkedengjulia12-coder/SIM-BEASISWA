@extends('layouts.app')
@section('title', 'Skema Beasiswa')
@section('page-title', 'Skema Beasiswa')
@section('page-subtitle', 'Konfigurasi jenis, kuota, kriteria seleksi, periode, dan aturan penyaluran (P-05)')

@section('content')
<details class="mb-6 rounded-xl border border-slate-200 bg-white shadow-sm" @if ($errors->any() && ! old('_edit')) open @endif>
    <summary class="cursor-pointer px-5 py-3 text-sm font-semibold text-red-700">+ Tambah Skema Beasiswa</summary>
    <form method="POST" action="{{ route('skema.store') }}" class="grid gap-4 border-t border-slate-100 p-5 sm:grid-cols-2 lg:grid-cols-3">
        @csrf
        @include('skema._form', ['s' => null])
        <div class="sm:col-span-2 lg:col-span-3"><button class="rounded-lg bg-red-700 px-5 py-2 text-sm font-semibold text-white hover:bg-red-800">Simpan sebagai Draf</button></div>
    </form>
</details>

<div class="space-y-4">
@forelse ($skema as $s)
    @php
        $kebutuhan = $s->kebutuhan_dana;
        $tersedia = $s->sisaKomitmen();
        $cukup = $kebutuhan <= $tersedia;
        $b = $s->bobot;
    @endphp
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-mono text-slate-400">{{ $s->id_skema }}</p>
                <h3 class="text-lg font-semibold text-slate-800">{{ $s->nama_skema }}</h3>
                <p class="text-sm text-slate-500">{{ $s->label_jenis }} &middot; IPK min. {{ number_format($s->ipk_minimum, 2) }} &middot; {{ $aturan[$s->aturan_penyaluran] }} ({{ $s->jumlah_termin }} termin)</p>
            </div>
            <x-status-badge :s="$s->status" />
        </div>

        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-4">
            <div><dt class="text-xs text-slate-500">Kuota / Pendaftar / Penerima</dt><dd class="font-semibold">{{ $s->kuota }} / {{ $s->pendaftaran_count }} / {{ $s->penerima_count }}</dd></div>
            <div><dt class="text-xs text-slate-500">Nominal per termin</dt><dd class="font-semibold">{{ \App\Support\Kode::rupiah($s->nominal_per_penerima) }}</dd></div>
            <div><dt class="text-xs text-slate-500">Periode pendaftaran</dt><dd class="font-semibold">{{ $s->tgl_buka->format('d-m-Y') }} s.d. {{ $s->tgl_tutup->format('d-m-Y') }}</dd></div>
            <div><dt class="text-xs text-slate-500">Bobot (Akd / Non / Keb)</dt><dd class="font-semibold">{{ (int) $b['akademik'] }}% / {{ (int) $b['nonakademik'] }}% / {{ (int) $b['kebutuhan'] }}%</dd></div>
        </dl>

        @if (in_array($s->status, ['draf', 'dibuka']))
            <p class="mt-3 text-xs {{ $cukup ? 'text-emerald-700' : 'text-rose-700' }}">
            Kebutuhan dana {{ \App\Support\Kode::rupiah($kebutuhan) }} &middot; sisa komitmen terkait {{ \App\Support\Kode::rupiah($tersedia) }}
            &mdash; {{ $cukup ? 'dana mencukupi' : 'dana kurang (tautkan komitmen dana ke skema ini)' }}
        </p>
        @endif

        <div class="mt-4 flex flex-wrap items-start gap-2">
            @if (in_array($s->status, ['draf', 'ditutup']) && $s->penerima_count == 0)
                <form method="POST" action="{{ route('skema.buka', $s) }}">@csrf<button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">Buka Pendaftaran</button></form>
            @endif
            @if ($s->status === 'dibuka')
                <form method="POST" action="{{ route('skema.tutup', $s) }}">@csrf<button class="rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-700">Tutup Pendaftaran</button></form>
            @endif
            @if ($s->status === 'ditutup')
                <form method="POST" action="{{ route('skema.selesai', $s) }}">@csrf<button class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Tandai Selesai</button></form>
            @endif
            @if ($s->penerima_count == 0)
                <details class="w-full">
                    <summary class="cursor-pointer text-xs font-semibold text-red-700">Ubah skema</summary>
                    <form method="POST" action="{{ route('skema.update', $s) }}" class="mt-3 grid gap-4 rounded-lg border border-slate-200 p-4 sm:grid-cols-2 lg:grid-cols-3">
                        @csrf @method('PUT')
                        @include('skema._form', ['s' => $s])
                        <div class="sm:col-span-2 lg:col-span-3"><button class="rounded-lg bg-red-700 px-5 py-2 text-sm font-semibold text-white hover:bg-red-800">Simpan Perubahan</button></div>
                    </form>
                </details>
            @endif
        </div>
    </div>
@empty
    <p class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-slate-400">Belum ada skema beasiswa.</p>
@endforelse
</div>
@endsection
