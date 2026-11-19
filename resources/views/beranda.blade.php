@extends('layouts.app')
@section('title', 'Beranda')
@section('page-title', 'Beranda Staf Unit Beasiswa')
@section('page-subtitle', 'Ringkasan pekerjaan yang menunggu tindakan Anda')

@section('content')
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @foreach ($tugas as $t)
        <a href="{{ route($t['route']) }}" class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-red-300 hover:shadow">
            <p class="text-sm text-slate-500">{{ $t['label'] }}</p>
            <p class="mt-2 text-3xl font-bold {{ $t['jumlah'] > 0 ? 'text-red-700' : 'text-slate-400' }}">{{ $t['jumlah'] }}</p>
            <p class="mt-2 text-xs font-semibold text-red-700 group-hover:underline">Buka halaman &rarr;</p>
        </a>
    @endforeach
</div>
@endsection
