@extends('layouts.app')
@section('title', 'Profil & Rekening')
@section('page-title', 'Profil dan Rekening')
@section('page-subtitle', 'Data rekening dibutuhkan untuk penyaluran dana dan disimpan terenkripsi')

@section('content')
@if (! $mhs)
    <p class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">Profil mahasiswa Anda belum terdaftar. Hubungi admin.</p>
@else
<div class="grid gap-6 lg:grid-cols-2">
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-3 font-semibold text-slate-800">Data Akademik</h2>
        <dl class="grid grid-cols-2 gap-3 text-sm">
            <div><dt class="text-xs text-slate-500">NPM</dt><dd class="font-semibold">{{ $mhs->npm }}</dd></div>
            <div><dt class="text-xs text-slate-500">Nama</dt><dd class="font-semibold">{{ $mhs->nama_lengkap }}</dd></div>
            <div><dt class="text-xs text-slate-500">Program Studi</dt><dd class="font-semibold">{{ $mhs->program_studi }}</dd></div>
            <div><dt class="text-xs text-slate-500">Fakultas</dt><dd class="font-semibold">{{ $mhs->fakultas }}</dd></div>
            <div><dt class="text-xs text-slate-500">Angkatan</dt><dd class="font-semibold">{{ $mhs->angkatan }}</dd></div>
            <div><dt class="text-xs text-slate-500">IPK</dt><dd class="font-semibold">{{ number_format($mhs->ipk, 2) }}</dd></div>
            <div><dt class="text-xs text-slate-500">Status Akademik</dt><dd><x-status-badge :s="$mhs->status_akademik" /></dd></div>
        </dl>
    </div>

    <form method="POST" action="{{ route('profil.update') }}" class="space-y-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        @csrf @method('PUT')
        <h2 class="font-semibold text-slate-800">Rekening Penerima Beasiswa</h2>
        <p class="text-xs text-slate-500">Rekening saat ini: <b>{{ $mhs->rekening_tersamar }}</b> ({{ $mhs->nama_bank ?? 'belum diisi' }})</p>
        <x-field label="Nama Bank" name="nama_bank" :value="$mhs->nama_bank" :keep="true" required />
        <x-field label="Nomor Rekening" name="no_rekening" inputmode="numeric" placeholder="6-20 digit angka" required />
        <button class="rounded-lg bg-red-700 px-5 py-2 text-sm font-semibold text-white hover:bg-red-800">Simpan Rekening</button>
    </form>
</div>
@endif
@endsection
