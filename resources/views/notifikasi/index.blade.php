@extends('layouts.app')
@section('title', 'Notifikasi')
@section('page-title', 'Notifikasi')
@section('page-subtitle', 'Pemberitahuan masa berlaku kontrak, seleksi, dan penyaluran dana')

@section('content')
<form method="POST" action="{{ route('notifikasi.baca-semua') }}" class="mb-4">@csrf
    <button class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Tandai semua sudah dibaca</button>
</form>

<div class="space-y-3">
    @forelse ($daftar as $n)
        <div class="rounded-xl border bg-white p-4 shadow-sm {{ $n->dibaca ? 'border-slate-200' : 'border-red-300 bg-red-50/40' }}">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="font-semibold text-slate-800">{{ $n->judul }} @unless ($n->dibaca)<span class="ml-1 rounded-full bg-red-600 px-2 py-0.5 text-[10px] font-bold text-white">BARU</span>@endunless</p>
                    <p class="mt-1 whitespace-pre-line text-sm text-slate-600">{{ $n->isi }}</p>
                    <p class="mt-2 text-xs text-slate-400">{{ ucfirst(str_replace('_', ' ', $n->jenis)) }} &middot; {{ $n->tgl_kirim->format('d-m-Y H:i') }}</p>
                </div>
                @unless ($n->dibaca)
                    <form method="POST" action="{{ route('notifikasi.baca', $n->id_notifikasi) }}">@csrf<button class="shrink-0 text-xs font-semibold text-red-700 hover:underline">Tandai dibaca</button></form>
                @endunless
            </div>
        </div>
    @empty
        <p class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-slate-400">Belum ada notifikasi.</p>
    @endforelse
</div>
<div class="mt-4">{{ $daftar->links() }}</div>
@endsection
