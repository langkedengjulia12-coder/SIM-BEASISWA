@extends('layouts.app')
@section('title', 'Verifikasi Berkas')
@section('page-title', 'Verifikasi Berkas Pendaftar')
@section('page-subtitle', 'Periksa kelengkapan dan keabsahan dokumen pendaftar (P-07)')

@section('content')
<form method="GET" class="mb-4 flex flex-wrap gap-2">
    <select name="status" onchange="this.form.submit()" class="rounded-lg border-slate-300 text-sm focus:border-red-600 focus:ring-red-600">
        @foreach (['diajukan' => 'Menunggu verifikasi', 'tidak_lengkap' => 'Tidak lengkap', 'lolos_administrasi' => 'Lolos administrasi', 'semua' => 'Semua status'] as $k => $v)
            <option value="{{ $k }}" @selected($status == $k)>{{ $v }}</option>
        @endforeach
    </select>
    <select name="skema" onchange="this.form.submit()" class="rounded-lg border-slate-300 text-sm focus:border-red-600 focus:ring-red-600">
        <option value="">Semua skema</option>
        @foreach ($skemaPilihan as $s)<option value="{{ $s->id_skema }}" @selected($skemaDipilih == $s->id_skema)>{{ $s->nama_skema }}</option>@endforeach
    </select>
</form>

<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50"><tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
            <th class="px-3 py-2">No. Pendaftaran</th><th class="px-3 py-2">Mahasiswa</th><th class="px-3 py-2">Skema</th><th class="px-3 py-2">IPK</th><th class="px-3 py-2">Berkas</th><th class="px-3 py-2">Status</th><th class="px-3 py-2"></th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
        @forelse ($daftar as $p)
            <tr>
                <td class="px-3 py-2 font-mono text-xs">{{ $p->id_pendaftaran }}<br><span class="font-sans text-slate-400">{{ $p->tgl_daftar->format('d-m-Y H:i') }}</span></td>
                <td class="px-3 py-2 font-medium">{{ $p->mahasiswa->nama_lengkap }}<br><span class="text-xs font-normal text-slate-500">{{ $p->npm }} &middot; {{ $p->mahasiswa->program_studi }}</span></td>
                <td class="px-3 py-2">{{ $p->skema->nama_skema }}</td>
                <td class="px-3 py-2">{{ number_format($p->ipk_saat_daftar, 2) }}</td>
                <td class="px-3 py-2">{{ $p->dokumen_count }} dokumen</td>
                <td class="px-3 py-2"><x-status-badge :s="$p->status" /></td>
                <td class="px-3 py-2"><a href="{{ route('verifikasi.show', $p) }}" class="rounded-lg bg-red-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-800">{{ $p->status === 'diajukan' ? 'Verifikasi' : 'Lihat' }}</a></td>
            </tr>
        @empty
            <tr><td colspan="7" class="px-3 py-6 text-center text-slate-400">Tidak ada pendaftaran pada filter ini.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
