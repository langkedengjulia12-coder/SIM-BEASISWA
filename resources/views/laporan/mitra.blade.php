@extends('layouts.app')
@section('title', 'Laporan Penyaluran')
@section('page-title', 'Laporan Penyaluran Dana untuk Mitra / Donor')
@section('page-subtitle', 'Realisasi penyaluran dana pada skema yang Anda danai (P-13)')

@section('content')
<form method="POST" action="{{ route('laporan.tampil') }}" class="grid gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:grid-cols-3">
    @csrf
    <x-field label="Periode Awal" name="periode_awal" type="date" :value="now()->startOfYear()->toDateString()" :keep="true" required />
    <x-field label="Periode Akhir" name="periode_akhir" type="date" :value="now()->toDateString()" :keep="true" required />
    <div class="flex items-end"><button class="rounded-lg bg-red-700 px-5 py-2 text-sm font-semibold text-white hover:bg-red-800">Tampilkan Laporan</button></div>
</form>

@include('laporan._hasil')
@endsection
