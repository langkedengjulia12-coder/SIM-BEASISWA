@extends('layouts.app')

@section('title', 'Pelaporan & Audit')
@section('page-title', 'Pelaporan & Audit')
@section('page-subtitle', 'Unduh laporan resmi dan telusuri jejak aktivitas pengguna (audit trail)')

@section('content')
<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    @foreach ($reports as $r)
        <div class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-red-50 text-red-700">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-800">{{ $r['nama'] }}</h3>
                    <p class="text-xs text-slate-500">{{ $r['periode'] }} &middot; {{ $r['format'] }}</p>
                    <p class="text-xs text-slate-400">Diperbarui {{ $r['update'] }}</p>
                </div>
            </div>
            <button type="button" class="shrink-0 rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">Unduh</button>
        </div>
    @endforeach
</div>

<div class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
        <h2 class="font-semibold text-slate-800">Audit Trail Terbaru</h2>
        <select class="rounded-lg border-slate-300 py-1.5 text-sm focus:border-red-600 focus:ring-red-600">
            <option>Semua Modul</option>
            <option>Kemitraan</option>
            <option>Skema</option>
            <option>Seleksi</option>
            <option>Penyaluran</option>
            <option>Pelaporan</option>
        </select>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3">Waktu</th>
                    <th class="px-5 py-3">Pengguna</th>
                    <th class="px-5 py-3">Aktivitas</th>
                    <th class="px-5 py-3">Modul</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($auditLogs as $log)
                    <tr class="hover:bg-slate-50">
                        <td class="whitespace-nowrap px-5 py-3 text-slate-500">{{ $log['waktu'] }}</td>
                        <td class="px-5 py-3 font-mono text-xs text-slate-700">{{ $log['user'] }}</td>
                        <td class="px-5 py-3 text-slate-800">{{ $log['aksi'] }}</td>
                        <td class="px-5 py-3">
                            <span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">{{ $log['modul'] }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
