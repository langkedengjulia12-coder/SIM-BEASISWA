@extends('layouts.app')
@section('title', 'Daftar Beasiswa')
@section('page-title', 'Formulir Pendaftaran Beasiswa')
@section('page-subtitle', 'Pilih skema yang sedang dibuka, lengkapi motivasi, dan unggah dokumen wajib (P-06)')

@section('content')
@if (! $mhs)
    <p class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">Profil mahasiswa Anda belum terdaftar. Hubungi admin.</p>
@else
<div class="mb-5 rounded-xl border border-slate-200 bg-white p-4 text-sm shadow-sm">
    <span class="font-semibold">{{ $mhs->nama_lengkap }}</span> &middot; NPM {{ $mhs->npm }} &middot; {{ $mhs->program_studi }} &middot; IPK <b>{{ number_format($mhs->ipk, 2) }}</b>
</div>

<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
    @forelse ($skemaDibuka as $s)
        @php $sudahDaftar = in_array($s->id_skema, $sudah); $ipkKurang = $mhs->ipk < $s->ipk_minimum; @endphp
        <div class="rounded-xl border bg-white p-5 shadow-sm {{ $dipilih?->id_skema === $s->id_skema ? 'border-red-600 ring-1 ring-red-600' : 'border-slate-200' }}">
            <p class="text-xs text-slate-500">{{ $s->label_jenis }}</p>
            <h3 class="font-semibold text-slate-800">{{ $s->nama_skema }}</h3>
            <p class="mt-2 text-sm text-slate-600">{{ \App\Support\Kode::rupiah($s->nominal_per_penerima) }} / termin &middot; kuota {{ $s->kuota }}</p>
            <p class="text-xs text-slate-500">IPK min. {{ number_format($s->ipk_minimum, 2) }} &middot; tutup {{ $s->tgl_tutup->format('d-m-Y') }}</p>
            <div class="mt-3">
                @if ($sudahDaftar)<span class="text-xs font-semibold text-emerald-700">Anda sudah mendaftar pada skema ini.</span>
                @elseif ($ipkKurang)<span class="text-xs font-semibold text-rose-700">IPK Anda belum memenuhi syarat minimum skema.</span>
                @else<a href="{{ route('pendaftaran.form', ['skema' => $s->id_skema]) }}" class="rounded-lg bg-red-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-800">Pilih &amp; Daftar</a>@endif
            </div>
        </div>
    @empty
        <p class="col-span-full rounded-xl border border-dashed border-slate-300 p-8 text-center text-slate-400">Saat ini tidak ada skema beasiswa yang membuka pendaftaran.</p>
    @endforelse
</div>

@if ($dipilih && ! in_array($dipilih->id_skema, $sudah) && $mhs->ipk >= $dipilih->ipk_minimum)
    <form method="POST" action="{{ route('pendaftaran.kirim') }}" enctype="multipart/form-data" class="mt-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        @csrf
        <input type="hidden" name="id_skema" value="{{ $dipilih->id_skema }}">
        <h2 class="font-semibold text-slate-800">Pendaftaran: {{ $dipilih->nama_skema }}</h2>

        <div class="mt-4">
            <label class="mb-1 block text-xs font-semibold text-slate-600">Motivasi / ringkasan proposal</label>
            <textarea name="motivasi" rows="4" class="block w-full rounded-lg border-slate-300 text-sm focus:border-red-600 focus:ring-red-600">{{ old('motivasi') }}</textarea>
        </div>

        <h3 class="mb-2 mt-5 text-sm font-semibold text-slate-700">Dokumen wajib (PDF / JPG / PNG, maks. 5 MB per berkas)</h3>
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($dipilih->dokumen_wajib as $j)
                <x-field :label="$label[$j] ?? $j" :name="'dokumen['.$j.']'" type="file" accept=".pdf,.jpg,.jpeg,.png" required />
            @endforeach
        </div>
        <button class="mt-5 rounded-lg bg-red-700 px-5 py-2 text-sm font-semibold text-white hover:bg-red-800">Kirim Pendaftaran</button>
    </form>
@endif
@endif
@endsection
