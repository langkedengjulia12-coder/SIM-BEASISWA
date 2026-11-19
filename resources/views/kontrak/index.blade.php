@extends('layouts.app')
@section('title', 'Kontrak Kerja Sama')
@section('page-title', 'Kontrak Kerja Sama (MoU / MoA)')
@section('page-subtitle', 'Pencatatan kontrak, masa berlaku, dan perpanjangan (P-03)')

@section('content')
<details class="mb-6 rounded-xl border border-slate-200 bg-white shadow-sm" @if ($errors->any()) open @endif>
    <summary class="cursor-pointer px-5 py-3 text-sm font-semibold text-red-700">+ Tambah Kontrak</summary>
    <form method="POST" action="{{ route('kontrak.store') }}" enctype="multipart/form-data" class="grid gap-4 border-t border-slate-100 p-5 sm:grid-cols-2 lg:grid-cols-3">
        @csrf
        <x-select-field label="Mitra (terverifikasi)" name="id_mitra" required>
            <option value="">- pilih mitra -</option>
            @foreach ($mitra as $m)<option value="{{ $m->id_mitra }}" @selected(old('id_mitra') == $m->id_mitra)>{{ $m->nama_mitra }}</option>@endforeach
        </x-select-field>
        <x-field label="Nomor Dokumen" name="no_dokumen" required />
        <x-field label="Judul Kerja Sama" name="judul" required />
        <x-select-field label="Jenis Dokumen" name="jenis_dokumen" required>
            <option value="MoU" @selected(old('jenis_dokumen') == 'MoU')>MoU</option><option value="MoA" @selected(old('jenis_dokumen') == 'MoA')>MoA</option>
        </x-select-field>
        <x-field label="Tanggal Mulai" name="tgl_mulai" type="date" required />
        <x-field label="Tanggal Berakhir" name="tgl_berakhir" type="date" required />
        <x-field label="Nilai Komitmen (Rp)" name="nilai_komitmen" type="number" min="0" step="1" required />
        <x-select-field label="Status Awal" name="status" required>
            <option value="draf" @selected(old('status') == 'draf')>Draf</option><option value="aktif" @selected(old('status') == 'aktif')>Aktif</option>
        </x-select-field>
        <x-field label="Berkas Kontrak (PDF/DOC, maks 5 MB)" name="file_dokumen" type="file" accept=".pdf,.doc,.docx" />
        <div class="sm:col-span-2 lg:col-span-3"><button class="rounded-lg bg-red-700 px-5 py-2 text-sm font-semibold text-white hover:bg-red-800">Simpan</button></div>
    </form>
</details>

<form method="GET" class="mb-4">
    <select name="status" onchange="this.form.submit()" class="rounded-lg border-slate-300 text-sm focus:border-red-600 focus:ring-red-600">
        <option value="">Semua status</option>
        @foreach (['draf', 'aktif', 'diperpanjang', 'berakhir'] as $s)<option value="{{ $s }}" @selected(request('status') == $s)>{{ ucfirst($s) }}</option>@endforeach
    </select>
</form>

<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50"><tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
            <th class="px-3 py-2">ID / No. Dokumen</th><th class="px-3 py-2">Mitra &amp; Judul</th><th class="px-3 py-2">Masa Berlaku</th><th class="px-3 py-2 text-right">Nilai</th><th class="px-3 py-2">Status</th><th class="px-3 py-2">Aksi</th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
        @forelse ($kontrak as $k)
            <tr>
                <td class="px-3 py-2"><span class="font-mono text-xs">{{ $k->id_kontrak }}</span><br><span class="text-xs text-slate-500">{{ $k->no_dokumen }} ({{ $k->jenis_dokumen }})</span></td>
                <td class="px-3 py-2"><span class="font-medium">{{ $k->mitra->nama_mitra }}</span><br><span class="text-xs text-slate-500">{{ $k->judul }}</span></td>
                <td class="px-3 py-2">{{ $k->tgl_mulai->format('d-m-Y') }} s.d. {{ $k->tgl_berakhir->format('d-m-Y') }}
                    @if (in_array($k->status, ['aktif', 'diperpanjang']) && $k->sisa_hari <= 30 && $k->sisa_hari >= 0)
                        <br><span class="text-xs font-semibold text-amber-700">Kontrak akan berakhir dalam {{ $k->sisa_hari }} hari.</span>
                    @endif
                </td>
                <td class="px-3 py-2 text-right">{{ \App\Support\Kode::rupiah($k->nilai_komitmen) }}</td>
                <td class="px-3 py-2"><x-status-badge :s="$k->status" /></td>
                <td class="px-3 py-2">
                    <div class="flex flex-wrap items-start gap-2">
                        @if ($k->file_dokumen)<a href="{{ route('kontrak.berkas', $k) }}" target="_blank" class="text-xs font-semibold text-sky-700 hover:underline">Berkas</a>@endif
                        @if ($k->status === 'draf')
                            <form method="POST" action="{{ route('kontrak.aktifkan', $k) }}">@csrf<button class="rounded-lg bg-emerald-600 px-3 py-1 text-xs font-semibold text-white hover:bg-emerald-700">Aktifkan</button></form>
                        @endif
                        @if ($k->status !== 'draf')
                            <details class="relative">
                                <summary class="cursor-pointer text-xs font-semibold text-red-700">Perpanjang</summary>
                                <form method="POST" action="{{ route('kontrak.perpanjang', $k) }}" class="absolute right-0 z-10 mt-2 w-64 space-y-3 rounded-lg border border-slate-200 bg-white p-4 shadow-lg">
                                    @csrf
                                    <x-field label="Berakhir baru" name="tgl_berakhir" type="date" required />
                                    <button class="w-full rounded-lg bg-red-700 py-2 text-sm font-semibold text-white hover:bg-red-800">Perpanjang</button>
                                </form>
                            </details>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-3 py-6 text-center text-slate-400">Belum ada kontrak.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
