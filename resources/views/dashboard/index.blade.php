@extends('layouts.app')
@section('title', 'Dashboard Admin WR3')
@section('page-title', 'Dashboard Admin Wakil Rektor 3')
@section('page-subtitle', 'Ringkasan kemitraan, penerima beasiswa, dan realisasi penyaluran dana')

@section('content')
@php
    $maks = max(array_merge([1], $perBulan));
    $persen = $realisasiKomitmen['komitmen'] > 0 ? round($realisasiKomitmen['diterima'] / $realisasiKomitmen['komitmen'] * 100) : 0;
@endphp

<form method="GET" class="mb-5 flex items-center gap-2">
    <label for="tahun" class="text-sm font-medium text-slate-600">Tahun</label>
    <select id="tahun" name="tahun" onchange="this.form.submit()" class="rounded-lg border-slate-300 py-1.5 text-sm focus:border-red-600 focus:ring-red-600">
        @for ($t = now()->year + 1; $t >= now()->year - 4; $t--)
            <option value="{{ $t }}" @selected($t == $tahun)>{{ $t }}</option>
        @endfor
    </select>
</form>

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ([
        ['Penerima Aktif', number_format($kartu['penerima_aktif'], 0, ',', '.'), 'text-red-700'],
        ['Dana Tersalurkan '.$tahun, \App\Support\Kode::rupiah($kartu['dana_tersalur']), 'text-emerald-700'],
        ['Kontrak Aktif', $kartu['kontrak_aktif'], 'text-sky-700'],
        ['Mitra Terverifikasi', $kartu['mitra_terverifikasi'], 'text-indigo-700'],
    ] as [$label, $nilai, $warna])
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">{{ $label }}</p>
            <p class="mt-2 text-2xl font-bold {{ $warna }}">{{ $nilai }}</p>
        </div>
    @endforeach
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2">
        <h2 class="font-semibold text-slate-800">Dana Tersalurkan per Bulan ({{ $tahun }})</h2>
        @if ($kosong)
            <p class="mt-6 rounded-lg bg-slate-50 p-6 text-center text-sm text-slate-500">Belum ada data untuk tahun terpilih.</p>
        @else
            <svg viewBox="0 0 600 220" class="mt-4 w-full" role="img" aria-label="Grafik dana tersalurkan per bulan">
                @foreach ($perBulan as $bln => $nilai)
                    @php
                        $h = $nilai / $maks * 150;
                        $x = 20 + ($bln - 1) * 48;
                    @endphp
                    <rect x="{{ $x }}" y="{{ 170 - $h }}" width="32" height="{{ $h }}" rx="3" fill="#B30000" opacity="{{ $nilai > 0 ? 1 : .15 }}"/>
                    @if ($nilai > 0)
                        <text x="{{ $x + 16 }}" y="{{ 164 - $h }}" text-anchor="middle" font-size="9" fill="#475569">{{ number_format($nilai / 1000000, $nilai >= 10000000 ? 0 : 1, ',', '.') }}jt</text>
                    @endif
                    <text x="{{ $x + 16 }}" y="190" text-anchor="middle" font-size="11" fill="#64748b">{{ $namaBulan[$bln - 1] }}</text>
                @endforeach
                <line x1="14" y1="170" x2="596" y2="170" stroke="#cbd5e1"/>
            </svg>
        @endif
    </div>

    <div class="space-y-6">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-semibold text-slate-800">Realisasi Dana Komitmen</h2>
            <p class="mt-2 text-sm text-slate-500">{{ \App\Support\Kode::rupiah($realisasiKomitmen['diterima']) }} dari {{ \App\Support\Kode::rupiah($realisasiKomitmen['komitmen']) }}</p>
            <div class="mt-3 h-3 w-full overflow-hidden rounded-full bg-slate-100"><div class="h-3 rounded-full bg-red-700" style="width: {{ $persen }}%"></div></div>
            <p class="mt-1 text-right text-xs font-semibold text-slate-600">{{ $persen }}% diterima</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-semibold text-slate-800">Penerima per Jenis Beasiswa</h2>
            <ul class="mt-3 space-y-1.5 text-sm">
                @forelse ($perJenis as $jenis => $n)
                    <li class="flex justify-between"><span class="text-slate-600">{{ $jenis }}</span><span class="font-semibold">{{ $n }}</span></li>
                @empty
                    <li class="text-slate-400">Belum ada penerima.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="font-semibold text-slate-800">Kontrak Berakhir &le; 30 Hari</h2>
        <ul class="mt-3 divide-y divide-slate-100 text-sm">
            @forelse ($kontrakHampirBerakhir as $k)
                <li class="flex items-center justify-between py-2">
                    <span><span class="font-medium">{{ $k->mitra->nama_mitra }}</span><br><span class="text-xs text-slate-500">{{ $k->no_dokumen }}</span></span>
                    <span class="text-right text-xs {{ $k->sisa_hari <= 7 ? 'font-semibold text-red-600' : 'text-amber-700' }}">{{ $k->tgl_berakhir->format('d-m-Y') }}<br>{{ $k->sisa_hari }} hari lagi</span>
                </li>
            @empty
                <li class="py-2 text-slate-400">Tidak ada kontrak yang hampir berakhir.</li>
            @endforelse
        </ul>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="font-semibold text-slate-800">Aktivitas Terbaru</h2>
        <ul class="mt-3 divide-y divide-slate-100 text-sm">
            @forelse ($logTerbaru as $l)
                <li class="py-2"><span class="font-medium">{{ $l->pengguna->nama ?? 'Sistem' }}</span> <span class="text-slate-500">&middot; {{ $l->aksi }} {{ $l->modul }}</span><br><span class="text-xs text-slate-400">{{ $l->waktu->format('d-m-Y H:i') }} &middot; {{ \Illuminate\Support\Str::limit($l->detail, 70) }}</span></li>
            @empty
                <li class="py-2 text-slate-400">Belum ada aktivitas.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
