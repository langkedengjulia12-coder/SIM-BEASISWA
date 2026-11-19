@extends('layouts.app')
@section('title', 'Mitra & Donor')
@section('page-title', 'Data Mitra dan Donor')
@section('page-subtitle', 'Pendaftaran, verifikasi, dan pencarian mitra / donor beasiswa (P-02)')

@section('content')
<details class="mb-6 rounded-xl border border-slate-200 bg-white shadow-sm" @if ($errors->any()) open @endif>
    <summary class="cursor-pointer px-5 py-3 text-sm font-semibold text-red-700">+ Daftarkan Mitra / Donor</summary>
    <form method="POST" action="{{ route('mitra.store') }}" class="grid gap-4 border-t border-slate-100 p-5 sm:grid-cols-2 lg:grid-cols-3">
        @csrf
        <x-field label="Nama Mitra / Donor" name="nama_mitra" required />
        <x-select-field label="Jenis Mitra" name="jenis_mitra" required>
            @foreach ($jenis as $k => $v)<option value="{{ $k }}" @selected(old('jenis_mitra') == $k)>{{ $v }}</option>@endforeach
        </x-select-field>
        <x-field label="Nama PIC" name="nama_pic" required />
        <x-field label="Email PIC" name="email_pic" type="email" required />
        <x-field label="Telepon PIC" name="telepon_pic" />
        <div class="sm:col-span-2 lg:col-span-3">
            <label class="mb-1 block text-xs font-semibold text-slate-600">Alamat</label>
            <textarea name="alamat" rows="2" class="block w-full rounded-lg border-slate-300 text-sm focus:border-red-600 focus:ring-red-600">{{ old('alamat') }}</textarea>
        </div>
        <div class="sm:col-span-2 lg:col-span-3"><button class="rounded-lg bg-red-700 px-5 py-2 text-sm font-semibold text-white hover:bg-red-800">Simpan</button></div>
    </form>
</details>

<form method="GET" class="mb-4 flex flex-wrap gap-2">
    <input name="q" value="{{ request('q') }}" placeholder="Cari nama mitra / PIC" class="w-64 rounded-lg border-slate-300 text-sm focus:border-red-600 focus:ring-red-600">
    <select name="status" class="rounded-lg border-slate-300 text-sm focus:border-red-600 focus:ring-red-600">
        <option value="">Semua status</option>
        @foreach (['menunggu', 'terverifikasi', 'ditolak'] as $s)<option value="{{ $s }}" @selected(request('status') == $s)>{{ ucfirst($s) }}</option>@endforeach
    </select>
    <button class="rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cari</button>
</form>

<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50"><tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
            <th class="px-3 py-2">ID</th><th class="px-3 py-2">Mitra</th><th class="px-3 py-2">Jenis</th><th class="px-3 py-2">PIC</th><th class="px-3 py-2">Kontrak</th><th class="px-3 py-2">Status</th><th class="px-3 py-2">Aksi</th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
        @forelse ($mitra as $m)
            <tr>
                <td class="px-3 py-2 font-mono text-xs">{{ $m->id_mitra }}</td>
                <td class="px-3 py-2 font-medium">{{ $m->nama_mitra }}<br><span class="text-xs font-normal text-slate-500">Daftar {{ $m->tgl_daftar->format('d-m-Y') }}</span></td>
                <td class="px-3 py-2">{{ $m->label_jenis }}</td>
                <td class="px-3 py-2">{{ $m->nama_pic }}<br><span class="text-xs text-slate-500">{{ $m->email_pic }}</span></td>
                <td class="px-3 py-2">{{ $m->kontrak_count }}</td>
                <td class="px-3 py-2"><x-status-badge :s="$m->status_verifikasi" /></td>
                <td class="px-3 py-2">
                    <div class="flex flex-wrap items-start gap-2">
                        @if ($m->status_verifikasi === 'menunggu')
                            <form method="POST" action="{{ route('mitra.verifikasi', $m) }}">@csrf<input type="hidden" name="status" value="terverifikasi">
                                <button class="rounded-lg bg-emerald-600 px-3 py-1 text-xs font-semibold text-white hover:bg-emerald-700">Verifikasi</button></form>
                            <form method="POST" action="{{ route('mitra.verifikasi', $m) }}">@csrf<input type="hidden" name="status" value="ditolak">
                                <button class="rounded-lg border border-rose-300 px-3 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50">Tolak</button></form>
                        @endif
                        <details class="relative">
                            <summary class="cursor-pointer text-xs font-semibold text-red-700">Ubah</summary>
                            <form method="POST" action="{{ route('mitra.update', $m) }}" class="absolute right-0 z-10 mt-2 w-72 space-y-3 rounded-lg border border-slate-200 bg-white p-4 shadow-lg">
                                @csrf @method('PUT')
                                <x-field label="Nama Mitra" name="nama_mitra" :value="$m->nama_mitra" required />
                                <x-select-field label="Jenis" name="jenis_mitra">@foreach ($jenis as $k => $v)<option value="{{ $k }}" @selected($m->jenis_mitra == $k)>{{ $v }}</option>@endforeach</x-select-field>
                                <x-field label="Nama PIC" name="nama_pic" :value="$m->nama_pic" required />
                                <x-field label="Email PIC" name="email_pic" type="email" :value="$m->email_pic" required />
                                <x-field label="Telepon" name="telepon_pic" :value="$m->telepon_pic" />
                                <x-field label="Alamat" name="alamat" :value="$m->alamat" />
                                <button class="w-full rounded-lg bg-red-700 py-2 text-sm font-semibold text-white hover:bg-red-800">Simpan</button>
                            </form>
                        </details>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="px-3 py-6 text-center text-slate-400">Belum ada data mitra.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
