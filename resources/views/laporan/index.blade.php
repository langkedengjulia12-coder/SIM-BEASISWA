@extends('layouts.app')
@section('title', 'Laporan Realisasi')
@section('page-title', 'Laporan Realisasi Dana dan Laporan Mitra')
@section('page-subtitle', 'Agregasi nominal tersalurkan per skema dan mitra (P-13)')

@section('content')
<form method="POST" action="{{ route('laporan.tampil') }}" class="grid gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:grid-cols-2 lg:grid-cols-5" x-data="{ jenis: '{{ old('jenis', 'realisasi') }}' }">
    @csrf
    <x-select-field label="Jenis Laporan" name="jenis" x-model="jenis" required>
        @foreach ($jenisList as $k => $v)<option value="{{ $k }}" @selected(old('jenis') == $k)>{{ $v }}</option>@endforeach
    </x-select-field>
    <div x-show="jenis === 'mitra'" style="display:none">
        <x-select-field label="Mitra" name="id_mitra">
            <option value="">- pilih mitra -</option>
            @foreach ($mitraList as $m)<option value="{{ $m->id_mitra }}" @selected(old('id_mitra') == $m->id_mitra)>{{ $m->nama_mitra }}</option>@endforeach
        </x-select-field>
    </div>
    <x-field label="Periode Awal" name="periode_awal" type="date" :value="now()->startOfYear()->toDateString()" :keep="true" required />
    <x-field label="Periode Akhir" name="periode_akhir" type="date" :value="now()->toDateString()" :keep="true" required />
    <div class="flex items-end"><button class="rounded-lg bg-red-700 px-5 py-2 text-sm font-semibold text-white hover:bg-red-800">Tampilkan Laporan</button></div>
</form>

@include('laporan._hasil')
@endsection
