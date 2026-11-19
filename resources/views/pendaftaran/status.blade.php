@extends('layouts.app')
@section('title', 'Status Pendaftaran')
@section('page-title', 'Status Pendaftaran Saya')
@section('page-subtitle', 'Pantau perkembangan seleksi beasiswa yang Anda ajukan (P-10)')

@section('content')
@if (! $mhs)
    <p class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">Profil mahasiswa Anda belum terdaftar. Hubungi admin.</p>
@else
<div class="space-y-4">
    @forelse ($daftar as $p)
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="font-mono text-xs text-slate-400">{{ $p->id_pendaftaran }} &middot; {{ $p->tgl_daftar->format('d-m-Y H:i') }}</p>
                    <h3 class="text-lg font-semibold text-slate-800">{{ $p->skema->nama_skema }}</h3>
                </div>
                <x-status-badge :s="$p->status" class="text-sm" />
            </div>

            @php $tahap = ['diajukan' => 1, 'tidak_lengkap' => 1, 'lolos_administrasi' => 2, 'dinilai' => 3, 'diterima' => 4, 'cadangan' => 4, 'ditolak' => 4][$p->status]; @endphp
            <ol class="mt-4 grid grid-cols-4 gap-2 text-center text-xs">
                @foreach (['Diajukan', 'Lolos Administrasi', 'Dinilai', 'Hasil Akhir'] as $n => $t)
                    <li class="rounded-lg py-2 font-semibold {{ $n + 1 <= $tahap ? 'bg-red-700 text-white' : 'bg-slate-100 text-slate-400' }}">{{ $t }}</li>
                @endforeach
            </ol>

            @if ($p->catatan_verifikasi)
                <p class="mt-3 rounded-lg bg-slate-50 p-3 text-sm"><span class="font-semibold">Catatan verifikator:</span> {{ $p->catatan_verifikasi }}</p>
            @endif
            @if ($p->penilaian && in_array($p->status, ['dinilai', 'diterima', 'cadangan', 'ditolak']))
                <p class="mt-3 text-sm text-slate-600">Peringkat <b>{{ $p->penilaian->peringkat }}</b> &middot; Nilai total <b>{{ number_format($p->penilaian->nilai_total, 2) }}</b></p>
            @endif
            @if ($p->penerima)
                <p class="mt-2 text-sm font-semibold text-emerald-700">Selamat! Anda ditetapkan sebagai penerima (SK {{ $p->penerima->no_sk }}, {{ $p->penerima->tgl_penetapan->format('d-m-Y') }}).</p>
            @endif

            @if ($p->status === 'tidak_lengkap' && ! $p->skema->sudahDitetapkan())
                @php $salah = $p->dokumen->where('status_verifikasi', 'tidak_valid'); @endphp
                <form method="POST" action="{{ route('pendaftaran.perbaiki', $p) }}" enctype="multipart/form-data" class="mt-4 rounded-lg border border-orange-200 bg-orange-50 p-4">
                    @csrf
                    <p class="mb-3 text-sm font-semibold text-orange-900">Unggah ulang dokumen yang tidak valid:</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($salah as $d)
                            <x-field :label="$d->label" :name="'perbaikan['.\App\Support\Kode::kunci($d->id_dokumen).']'" type="file" accept=".pdf,.jpg,.jpeg,.png" required />
                        @endforeach
                    </div>
                    <button class="mt-3 rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">Kirim Perbaikan</button>
                </form>
            @endif
        </div>
    @empty
        <p class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-slate-400">Anda belum memiliki pendaftaran. <a href="{{ route('pendaftaran.form') }}" class="font-semibold text-red-700 hover:underline">Daftar beasiswa sekarang</a>.</p>
    @endforelse
</div>
@endif
@endsection
