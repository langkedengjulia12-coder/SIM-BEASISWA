@extends('layouts.app')
@section('title', 'Kelola Pengguna')
@section('page-title', 'Manajemen Pengguna')
@section('page-subtitle', 'Tambah akun, ubah data, reset password, dan aktifkan / nonaktifkan pengguna (P-17)')

@section('content')
<details class="mb-6 rounded-xl border border-slate-200 bg-white shadow-sm" @if ($errors->any()) open @endif>
    <summary class="cursor-pointer px-5 py-3 text-sm font-semibold text-red-700">+ Tambah Pengguna</summary>
    <form method="POST" action="{{ route('pengguna.store') }}" class="grid gap-4 border-t border-slate-100 p-5 sm:grid-cols-2 lg:grid-cols-3" x-data="{ role: '{{ old('role', 'staf_beasiswa') }}' }">
        @csrf
        <x-field label="Username" name="username" required />
        <x-field label="Nama" name="nama" required />
        <x-field label="Email" name="email" type="email" required />
        <x-select-field label="Peran" name="role" x-model="role" required>
            @foreach ($roleBaru as $r)<option value="{{ $r }}" @selected(old('role') == $r)>{{ $labelRole[$r] }}</option>@endforeach
        </x-select-field>
        <x-field label="Password awal (min. 8)" name="password" type="password" required />
        <template x-if="role === 'mahasiswa'">
            <div class="contents">
                <x-field label="NPM (11 digit)" name="npm" maxlength="11" />
                <x-field label="Program Studi" name="program_studi" />
                <x-field label="Fakultas" name="fakultas" />
                <x-field label="Angkatan" name="angkatan" type="number" min="2000" :max="now()->year" />
                <x-field label="IPK" name="ipk" type="number" step="0.01" min="0" max="4" />
            </div>
        </template>
        <div class="flex items-end sm:col-span-2 lg:col-span-3"><button class="rounded-lg bg-red-700 px-5 py-2 text-sm font-semibold text-white hover:bg-red-800">Simpan</button></div>
    </form>
</details>

<form method="GET" class="mb-4 flex flex-wrap gap-2">
    <input name="q" value="{{ request('q') }}" placeholder="Cari nama / username / email" class="w-64 rounded-lg border-slate-300 text-sm focus:border-red-600 focus:ring-red-600">
    <select name="role" class="rounded-lg border-slate-300 text-sm focus:border-red-600 focus:ring-red-600">
        <option value="">Semua peran</option>
        @foreach ($labelRole as $k => $v)<option value="{{ $k }}" @selected(request('role') == $k)>{{ $v }}</option>@endforeach
    </select>
    <button class="rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Filter</button>
</form>

<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50"><tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
            <th class="px-3 py-2">ID</th><th class="px-3 py-2">Username</th><th class="px-3 py-2">Nama</th><th class="px-3 py-2">Peran</th><th class="px-3 py-2">Status</th><th class="px-3 py-2">Login Terakhir</th><th class="px-3 py-2">Aksi</th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
        @forelse ($pengguna as $p)
            <tr>
                <td class="px-3 py-2 font-mono text-xs">{{ $p->id_pengguna }}</td>
                <td class="px-3 py-2">{{ $p->username }}</td>
                <td class="px-3 py-2">{{ $p->nama }}<br><span class="text-xs text-slate-500">{{ $p->email }}@if ($p->mahasiswa) &middot; NPM {{ $p->mahasiswa->npm }}@endif</span></td>
                <td class="px-3 py-2">{{ $p->label_role }}</td>
                <td class="px-3 py-2"><x-status-badge :s="$p->status_aktif ? 'aktif' : 'dihentikan'" />@unless ($p->status_aktif)<span class="ml-1 text-xs text-slate-500">nonaktif</span>@endunless</td>
                <td class="px-3 py-2 text-xs text-slate-500">{{ $p->last_login?->format('d-m-Y H:i') ?? '-' }}</td>
                <td class="px-3 py-2">
                    <details class="relative">
                        <summary class="cursor-pointer text-xs font-semibold text-red-700">Ubah</summary>
                        <form method="POST" action="{{ route('pengguna.update', $p) }}" class="absolute right-0 z-10 mt-2 w-72 space-y-3 rounded-lg border border-slate-200 bg-white p-4 shadow-lg">
                            @csrf @method('PUT')
                            <x-field label="Nama" name="nama" :value="$p->nama" required />
                            <x-field label="Email" name="email" type="email" :value="$p->email" required />
                            <x-field label="Reset password (opsional)" name="password" type="password" />
                            <x-select-field label="Status akun" name="status_aktif">
                                <option value="1" @selected($p->status_aktif)>Aktif</option>
                                <option value="0" @selected(! $p->status_aktif)>Nonaktif</option>
                            </x-select-field>
                            <button class="w-full rounded-lg bg-red-700 py-2 text-sm font-semibold text-white hover:bg-red-800">Simpan</button>
                        </form>
                    </details>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="px-3 py-6 text-center text-slate-400">Tidak ada pengguna.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
