@extends('layouts.app')
@section('title', 'Penetapan & Pengumuman')
@section('page-title', 'Penetapan dan Pengumuman Hasil Seleksi')
@section('page-subtitle', 'Tetapkan penerima sesuai kuota dan umumkan hasil ke seluruh pendaftar (P-09)')

@section('content')
<form method="GET" class="mb-5">
    <select name="skema" onchange="this.form.submit()" class="rounded-lg border-slate-300 text-sm focus:border-red-600 focus:ring-red-600">
        <option value="">- pilih skema -</option>
        @foreach ($skemaList as $s)<option value="{{ $s->id_skema }}" @selected($skema?->id_skema == $s->id_skema)>{{ $s->nama_skema }} ({{ $s->status }})</option>@endforeach
    </select>
</form>

@if ($skema)
    <div class="mb-4 grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><p class="text-xs text-slate-500">Kuota penerima</p><p class="text-xl font-bold">{{ $skema->kuota }}</p></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><p class="text-xs text-slate-500">Kuota cadangan ({{ config('beasiswa.cadangan_persen') }}%)</p><p class="text-xl font-bold">{{ $cadangan }}</p></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><p class="text-xs text-slate-500">Status skema</p><x-status-badge :s="$skema->status" /></div>
    </div>

    @if ($kendala)
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <ul class="list-disc space-y-1 pl-5">@foreach ($kendala as $k)<li>{{ $k }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <th class="px-3 py-2">Peringkat</th><th class="px-3 py-2">Mahasiswa</th><th class="px-3 py-2">Nilai Total</th><th class="px-3 py-2">Rencana / Hasil</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($peringkat as $p)
                @php
                    $r = $p->penilaian->peringkat;
                    $rencana = $r <= $skema->kuota ? 'diterima' : ($r <= $skema->kuota + $cadangan ? 'cadangan' : 'ditolak');
                    $tampil = in_array($p->status, ['diterima', 'cadangan', 'ditolak']) ? $p->status : $rencana;
                @endphp
                <tr>
                    <td class="px-3 py-2 font-bold text-red-700">{{ $r }}</td>
                    <td class="px-3 py-2 font-medium">{{ $p->mahasiswa->nama_lengkap }}<br><span class="text-xs font-normal text-slate-500">{{ $p->npm }}</span></td>
                    <td class="px-3 py-2">{{ number_format($p->penilaian->nilai_total, 2) }}</td>
                    <td class="px-3 py-2"><x-status-badge :s="$tampil" /></td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-3 py-6 text-center text-slate-400">Belum ada pendaftar yang dinilai.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if (empty($kendala))
        <form method="POST" action="{{ route('penetapan.tetapkan', $skema) }}" class="mt-6 grid gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:grid-cols-3"
              onsubmit="return confirm('Tetapkan penerima sebanyak kuota dan umumkan hasil?')">
            @csrf
            <x-field label="Nomor SK Penetapan" name="no_sk" required />
            <x-field label="Tanggal Penetapan" name="tgl_penetapan" type="date" :value="now()->toDateString()" :keep="true" required />
            <div class="flex items-end"><button class="rounded-lg bg-red-700 px-5 py-2 text-sm font-semibold text-white hover:bg-red-800">Tetapkan &amp; Umumkan</button></div>
        </form>
    @endif
@endif
@endsection
