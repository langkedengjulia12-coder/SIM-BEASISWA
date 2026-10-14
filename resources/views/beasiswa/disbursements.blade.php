@extends('layouts.app')

@section('title', 'Penyaluran & Rekonsiliasi')
@section('page-title', 'Penyaluran & Rekonsiliasi')
@section('page-subtitle', 'Monitoring pencairan dana beasiswa dan pencocokan dengan mutasi bank')

@section('content')
@php
    $tone = [
        'emerald' => 'border-t-red-600',
        'amber'   => 'border-t-red-600',
        'rose'    => 'border-t-red-600',
    ];
    $badge = [
        'Terekonsiliasi' => 'bg-emerald-100 text-emerald-800',
        'Menunggu'       => 'bg-amber-100 text-amber-800',
        'Selisih'        => 'bg-rose-100 text-rose-800',
    ];
@endphp

<div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
    @foreach ($summary as $item)
        <div class="rounded-xl border border-slate-200 border-t-4 bg-white p-5 shadow-sm {{ $tone[$item['tone']] ?? 'border-t-red-600' }}">
            <p class="text-sm text-slate-500">{{ $item['label'] }}</p>
            <p class="mt-2 text-2xl font-bold text-red-700">{{ $item['value'] }}</p>
        </div>
    @endforeach
</div>

<div class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="font-semibold text-slate-800">Batch Penyaluran</h2>
        <div class="flex gap-2">
            <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Impor Mutasi Bank</button>
            <button type="button" class="rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">+ Buat Batch</button>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3">Batch</th>
                    <th class="px-5 py-3">Tanggal</th>
                    <th class="px-5 py-3">Skema</th>
                    <th class="px-5 py-3 text-right">Penerima</th>
                    <th class="px-5 py-3 text-right">Nominal</th>
                    <th class="px-5 py-3">Bank</th>
                    <th class="px-5 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($disbursements as $d)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-mono text-xs text-slate-600">{{ $d['batch'] }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $d['tanggal'] }}</td>
                        <td class="px-5 py-3 font-medium text-slate-800">{{ $d['skema'] }}</td>
                        <td class="px-5 py-3 text-right text-slate-600">{{ number_format($d['penerima'], 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-slate-800">Rp {{ number_format($d['nominal'], 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $d['bank'] }}</td>
                        <td class="px-5 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge[$d['status']] ?? 'bg-slate-100 text-slate-700' }}">{{ $d['status'] }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-slate-50 text-sm font-semibold text-slate-800">
                <tr>
                    <td class="px-5 py-3" colspan="3">Total</td>
                    <td class="px-5 py-3 text-right">{{ number_format(collect($disbursements)->sum('penerima'), 0, ',', '.') }}</td>
                    <td class="px-5 py-3 text-right">Rp {{ number_format(collect($disbursements)->sum('nominal'), 0, ',', '.') }}</td>
                    <td class="px-5 py-3" colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
