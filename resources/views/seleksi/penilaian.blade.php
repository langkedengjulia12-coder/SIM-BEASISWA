@extends('layouts.app')
@section('title', 'Penilaian & Ranking')
@section('page-title', 'Penilaian dan Ranking Seleksi')
@section('page-subtitle', 'Input skor pendaftar yang lolos administrasi, lalu hitung ranking terbobot (P-08)')

@section('content')
<form method="GET" class="mb-5 flex items-center gap-2">
    <select name="skema" onchange="this.form.submit()" class="rounded-lg border-slate-300 text-sm focus:border-red-600 focus:ring-red-600">
        <option value="">- pilih skema -</option>
        @foreach ($skemaList as $s)<option value="{{ $s->id_skema }}" @selected($skema?->id_skema == $s->id_skema)>{{ $s->nama_skema }} ({{ $s->status }})</option>@endforeach
    </select>
</form>

@if ($skema)
    @php $b = $skema->bobot; @endphp
    <div class="mb-4 rounded-lg bg-slate-50 p-3 text-sm text-slate-600">
        Bobot kriteria: Akademik <b>{{ (int) $b['akademik'] }}%</b>, Prestasi/Non-akademik <b>{{ (int) $b['nonakademik'] }}%</b>, Kebutuhan/Proposal <b>{{ (int) $b['kebutuhan'] }}%</b>.
        Nilai total = jumlah (skor x bobot). Bila nilai sama, IPK lebih tinggi diutamakan.
    </div>

    @if ($baris->isEmpty())
        <p class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-slate-400">Belum ada pendaftar yang lolos administrasi pada skema ini.</p>
    @else
        <form method="POST" action="{{ route('penilaian.hitung', $skema) }}">
            @csrf
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50"><tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-3 py-2">Peringkat</th><th class="px-3 py-2">Mahasiswa</th><th class="px-3 py-2">IPK</th>
                        <th class="px-3 py-2">Akademik</th><th class="px-3 py-2">Non-akademik</th><th class="px-3 py-2">Kebutuhan</th><th class="px-3 py-2">Nilai Total</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                    @foreach ($baris as $p)
                        @php $n = $p->penilaian; @endphp
                        <tr>
                            <td class="px-3 py-2 text-center font-bold text-red-700">{{ $n?->peringkat ?? '-' }}</td>
                            <td class="px-3 py-2 font-medium">{{ $p->mahasiswa->nama_lengkap }}<br><span class="text-xs font-normal text-slate-500">{{ $p->npm }} &middot; {{ $p->id_pendaftaran }}</span></td>
                            <td class="px-3 py-2">{{ number_format($p->ipk_saat_daftar, 2) }}</td>
                            @foreach (['akademik' => 'nilai_akademik', 'nonakademik' => 'nilai_nonakademik', 'kebutuhan' => 'nilai_kebutuhan'] as $k => $kol)
                                <td class="px-3 py-2"><input type="number" name="skor[{{ \App\Support\Kode::kunci($p->id_pendaftaran) }}][{{ $k }}]" min="0" max="100" step="0.01" required
                                    value="{{ old('skor.'.\App\Support\Kode::kunci($p->id_pendaftaran).'.'.$k, $n?->{$kol}) }}" class="w-24 rounded-lg border-slate-300 py-1 text-sm focus:border-red-600 focus:ring-red-600" @disabled($skema->sudahDitetapkan())></td>
                            @endforeach
                            <td class="px-3 py-2 font-semibold">{{ $n ? number_format($n->nilai_total, 2) : '-' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @unless ($skema->sudahDitetapkan())
                <button class="mt-4 rounded-lg bg-red-700 px-5 py-2 text-sm font-semibold text-white hover:bg-red-800">Hitung Ranking</button>
            @else
                <p class="mt-4 text-sm text-slate-500">Hasil seleksi skema ini sudah ditetapkan; skor tidak dapat diubah.</p>
            @endunless
        </form>
    @endif
@endif
@endsection
