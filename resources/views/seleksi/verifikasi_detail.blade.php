@extends('layouts.app')
@section('title', 'Verifikasi '.$pendaftaran->id_pendaftaran)
@section('page-title', 'Verifikasi Berkas '.$pendaftaran->id_pendaftaran)
@section('page-subtitle', $pendaftaran->mahasiswa->nama_lengkap.' - '.$pendaftaran->skema->nama_skema)

@section('content')
@php $bisa = $pendaftaran->status === 'diajukan'; @endphp
<a href="{{ route('verifikasi.index') }}" class="mb-4 inline-block text-sm font-semibold text-red-700 hover:underline">&larr; Kembali ke daftar</a>

<div class="mb-6 grid gap-4 rounded-xl border border-slate-200 bg-white p-5 text-sm shadow-sm sm:grid-cols-2 lg:grid-cols-4">
    <div><p class="text-xs text-slate-500">NPM</p><p class="font-semibold">{{ $pendaftaran->npm }}</p></div>
    <div><p class="text-xs text-slate-500">Program Studi</p><p class="font-semibold">{{ $pendaftaran->mahasiswa->program_studi }}</p></div>
    <div><p class="text-xs text-slate-500">IPK saat daftar (min. {{ number_format($pendaftaran->skema->ipk_minimum, 2) }})</p><p class="font-semibold">{{ number_format($pendaftaran->ipk_saat_daftar, 2) }}</p></div>
    <div><p class="text-xs text-slate-500">Status</p><x-status-badge :s="$pendaftaran->status" /></div>
    @if ($pendaftaran->motivasi)<div class="sm:col-span-2 lg:col-span-4"><p class="text-xs text-slate-500">Motivasi / ringkasan</p><p>{{ $pendaftaran->motivasi }}</p></div>@endif
</div>

<form method="POST" action="{{ route('verifikasi.simpan', $pendaftaran) }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    @csrf
    <h2 class="mb-3 font-semibold text-slate-800">Dokumen Pendaftar</h2>
    <div class="divide-y divide-slate-100">
        @foreach ($pendaftaran->dokumen as $d)
            @php $nilai = old('hasil.'.\App\Support\Kode::kunci($d->id_dokumen), $d->status_verifikasi !== 'menunggu' ? $d->status_verifikasi : null); @endphp
            <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                <div>
                    <p class="font-medium">{{ $d->label }}</p>
                    <p class="text-xs text-slate-500">{{ $d->nama_file }} &middot; {{ $d->ukuran_kb }} KB &middot; <a href="{{ route('dokumen.unduh', $d) }}" target="_blank" class="font-semibold text-sky-700 hover:underline">Buka berkas</a></p>
                </div>
                @if ($bisa)
                    <div class="flex gap-4 text-sm">
                        <label class="flex items-center gap-1.5"><input type="radio" name="hasil[{{ \App\Support\Kode::kunci($d->id_dokumen) }}]" value="valid" @checked($nilai === 'valid') class="text-emerald-600 focus:ring-emerald-600"> Valid</label>
                        <label class="flex items-center gap-1.5"><input type="radio" name="hasil[{{ \App\Support\Kode::kunci($d->id_dokumen) }}]" value="tidak_valid" @checked($nilai === 'tidak_valid') class="text-rose-600 focus:ring-rose-600"> Tidak valid</label>
                    </div>
                @else
                    <x-status-badge :s="$d->status_verifikasi" />
                @endif
            </div>
        @endforeach
    </div>

    @if ($bisa)
        <div class="mt-4">
            <label class="mb-1 block text-xs font-semibold text-slate-600">Catatan verifikator (wajib bila ada dokumen tidak valid)</label>
            <textarea name="catatan" rows="3" class="block w-full rounded-lg border-slate-300 text-sm focus:border-red-600 focus:ring-red-600">{{ old('catatan') }}</textarea>
        </div>
        <button class="mt-4 rounded-lg bg-red-700 px-5 py-2 text-sm font-semibold text-white hover:bg-red-800">Simpan Hasil Verifikasi</button>
    @elseif ($pendaftaran->catatan_verifikasi)
        <p class="mt-4 rounded-lg bg-slate-50 p-3 text-sm"><span class="font-semibold">Catatan:</span> {{ $pendaftaran->catatan_verifikasi }}</p>
    @endif
</form>
@endsection
