@extends('layouts.app')

@section('title', 'Pelaporan & Audit')
@section('page-title', 'Pelaporan & Audit')
@section('page-subtitle', 'Unduh laporan resmi dan telusuri jejak aktivitas pengguna (audit trail)')

@section('content')
<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    @foreach ($reports as $r)
        <div class="flex flex-col items-start justify-between gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center">
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
            <div class="flex shrink-0 gap-2 sm:flex-col">
                <a href="{{ route('beasiswa.reports.export', ['report' => $r['slug'], 'format' => 'pdf']) }}" class="rounded-lg bg-red-700 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-red-800">PDF</a>
                <a href="{{ route('beasiswa.reports.export', ['report' => $r['slug'], 'format' => 'xlsx']) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">Excel</a>
            </div>
        </div>
    @endforeach
</div>

<div class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm">
<div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="font-semibold text-slate-800">Audit Trail Terbaru</h2>
    <form method="GET" action="{{ route('beasiswa.reports') }}" class="flex gap-2">
        <select name="module" class="rounded-lg border-slate-300 py-1.5 text-sm focus:border-red-600 focus:ring-red-600">
            <option value="">Semua Modul</option>
            @foreach (['Kemitraan', 'Skema', 'Seleksi', 'Penyaluran', 'Pelaporan'] as $module)
                <option value="{{ $module }}" @selected(request('module') === $module)>{{ $module }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Filter</button>
    </form>
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
                @forelse ($auditLogs as $log)
                    <tr class="hover:bg-slate-50">
                        <td class="whitespace-nowrap px-5 py-3 text-slate-500">{{ $log->created_at->format('d M Y H:i') }}</td>
                        <td class="px-5 py-3 font-mono text-xs text-slate-700">{{ $log->user_name }}</td>
                        <td class="px-5 py-3 text-slate-800">{{ $log->action }}</td>
                        <td class="px-5 py-3">
                            <span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">{{ $log->module }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-6 text-center text-sm text-slate-500">Belum ada aktivitas pada modul ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
