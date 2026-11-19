@extends('layouts.app')
@section('title', 'Ekspor Data')
@section('page-title', 'Ekspor Data Periodik')
@section('page-subtitle', 'Unduh laporan realisasi, mitra, akademik, atau komprehensif dalam format PDF / XLSX / CSV (P-14)')

@section('content')
<form method="POST" action="{{ route('ekspor.proses') }}" class="grid gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:grid-cols-2 lg:grid-cols-3" x-data="{ jenis: '{{ old('jenis', 'realisasi') }}' }">
    @csrf
    <x-select-field label="Jenis Data" name="jenis" x-model="jenis" required>
        @foreach ($jenisList as $k => $v)<option value="{{ $k }}" @selected(old('jenis') == $k)>{{ $v }}</option>@endforeach
    </x-select-field>
    <div x-show="jenis === 'mitra'" style="display:none">
        <x-select-field label="Mitra" name="id_mitra">
            <option value="">- pilih mitra -</option>
            @foreach ($mitraList as $m)<option value="{{ $m->id_mitra }}" @selected(old('id_mitra') == $m->id_mitra)>{{ $m->nama_mitra }}</option>@endforeach
        </x-select-field>
    </div>
    <x-select-field label="Format Berkas" name="format" required>
        @foreach (['PDF', 'XLSX', 'CSV'] as $f)<option value="{{ $f }}" @selected(old('format') == $f)>{{ $f }}</option>@endforeach
    </x-select-field>
    <x-field label="Periode Awal" name="periode_awal" type="date" :value="now()->startOfYear()->toDateString()" :keep="true" required />
    <x-field label="Periode Akhir" name="periode_akhir" type="date" :value="now()->toDateString()" :keep="true" required />
    <div class="flex items-end"><button class="rounded-lg bg-red-700 px-5 py-2 text-sm font-semibold text-white hover:bg-red-800">Ekspor &amp; Unduh</button></div>
</form>

<div class="mt-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    <h2 class="font-semibold text-slate-800">Riwayat Ekspor / Laporan</h2>
    <div class="mt-3 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <th class="px-3 py-2">ID</th><th class="px-3 py-2">Jenis</th><th class="px-3 py-2">Periode</th><th class="px-3 py-2">Format</th><th class="px-3 py-2">Pembuat</th><th class="px-3 py-2">Waktu</th><th class="px-3 py-2"></th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($riwayat as $r)
                <tr>
                    <td class="px-3 py-2 font-mono text-xs">{{ $r->id_laporan }}</td>
                    <td class="px-3 py-2">{{ ucfirst($r->jenis_laporan) }} {{ $r->mitra?->nama_mitra }}</td>
                    <td class="px-3 py-2">{{ $r->periode_awal->format('d-m-Y') }} s.d. {{ $r->periode_akhir->format('d-m-Y') }}</td>
                    <td class="px-3 py-2">{{ $r->format_file }}</td>
                    <td class="px-3 py-2">{{ $r->pembuat->nama ?? '-' }}</td>
                    <td class="px-3 py-2 text-xs text-slate-500">{{ $r->tgl_generate->format('d-m-Y H:i') }}</td>
                    <td class="px-3 py-2"><a href="{{ route('laporan.unduh', $r) }}" class="text-xs font-semibold text-sky-700 hover:underline">Unduh</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-3 py-6 text-center text-slate-400">Belum ada ekspor.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
