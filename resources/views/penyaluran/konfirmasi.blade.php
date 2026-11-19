@extends('layouts.app')
@section('title', 'Konfirmasi Dana')
@section('page-title', 'Konfirmasi Penerimaan Dana')
@section('page-subtitle', 'Riwayat penyaluran dana beasiswa Anda (P-12)')

@section('content')
@if (! $mhs)
    <p class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">Profil mahasiswa Anda belum terdaftar. Hubungi admin.</p>
@else
<div class="space-y-3">
    @forelse ($daftar as $p)
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div>
                <p class="font-semibold text-slate-800">{{ $p->penerima->skema->nama_skema }} - Termin {{ $p->termin_ke }}</p>
                <p class="text-sm text-slate-500">{{ \App\Support\Kode::rupiah($p->nominal) }} &middot; Ref: {{ $p->no_ref_keuangan ?? '-' }} &middot; Disalurkan: {{ $p->tgl_penyaluran?->format('d-m-Y') ?? '-' }}</p>
                @if ($p->tgl_konfirmasi)<p class="text-xs text-emerald-700">Dikonfirmasi {{ $p->tgl_konfirmasi->format('d-m-Y') }}</p>@endif
            </div>
            <div class="flex items-center gap-3">
                <x-status-badge :s="$p->status" />
                @if ($p->status === 'tersalurkan')
                    <form method="POST" action="{{ route('penyaluran.konfirmasi', $p) }}" onsubmit="return confirm('Konfirmasi bahwa dana sudah Anda terima?')">@csrf
                        <button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Saya sudah menerima</button></form>
                @endif
            </div>
        </div>
    @empty
        <p class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-slate-400">Belum ada penyaluran dana untuk Anda.</p>
    @endforelse
</div>
@endif
@endsection
