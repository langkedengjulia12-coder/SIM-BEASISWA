@extends('layouts.app')
@section('title', 'Penyaluran Dana')
@section('page-title', 'Verifikasi Penerima dan Penyaluran Dana')
@section('page-subtitle', 'Salurkan dana beasiswa per termin melalui integrasi sistem keuangan (P-11)')

@section('content')
<form method="GET" class="mb-5 flex flex-wrap items-end gap-3">
    <div>
        <label class="mb-1 block text-xs font-semibold text-slate-600">Skema</label>
        <select name="skema" onchange="this.form.submit()" class="rounded-lg border-slate-300 text-sm focus:border-red-600 focus:ring-red-600">
            <option value="">- pilih skema -</option>
            @foreach ($skemaList as $s)<option value="{{ $s->id_skema }}" @selected($skema?->id_skema == $s->id_skema)>{{ $s->nama_skema }}</option>@endforeach
        </select>
    </div>
    @if ($skema)
    <div>
        <label class="mb-1 block text-xs font-semibold text-slate-600">Termin</label>
        <select name="termin" onchange="this.form.submit()" class="rounded-lg border-slate-300 text-sm focus:border-red-600 focus:ring-red-600">
            @for ($t = 1; $t <= $skema->jumlah_termin; $t++)<option value="{{ $t }}" @selected($termin == $t)>Termin {{ $t }}</option>@endfor
        </select>
    </div>
    @endif
</form>

@if ($skema)
    @php $layak = $baris->where('layak', true)->count(); @endphp
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-sm text-slate-600">
            Nominal per penerima: <b>{{ \App\Support\Kode::rupiah($skema->nominal_per_penerima) }}</b> &middot;
            Layak disalurkan: <b class="text-emerald-700">{{ $layak }}</b> dari {{ $baris->count() }} penerima aktif
            @if (! config('beasiswa.keuangan.url'))<span class="ml-2 rounded bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">Mode simulasi API keuangan</span>@endif
        </p>
        <form method="POST" action="{{ route('penyaluran.salurkan') }}" onsubmit="return confirm('Kirim instruksi penyaluran untuk {{ $layak }} penerima?')">
            @csrf
            <input type="hidden" name="id_skema" value="{{ $skema->id_skema }}">
            <input type="hidden" name="termin_ke" value="{{ $termin }}">
            <button @disabled($layak === 0) class="rounded-lg bg-red-700 px-5 py-2 text-sm font-semibold text-white hover:bg-red-800 disabled:cursor-not-allowed disabled:opacity-40">Salurkan Dana</button>
        </form>
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <th class="px-3 py-2">Penerima</th><th class="px-3 py-2">Rekening</th><th class="px-3 py-2">Akademik</th><th class="px-3 py-2">Kelayakan</th><th class="px-3 py-2">Penyaluran Termin {{ $termin }}</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
            @foreach ($baris as $b)
                @php $p = $b['penerima']; $y = $b['penyaluran']; @endphp
                <tr>
                    <td class="px-3 py-2 font-medium">{{ $p->mahasiswa->nama_lengkap }}<br><span class="text-xs font-normal text-slate-500">{{ $p->npm }}</span></td>
                    <td class="px-3 py-2">{{ $p->mahasiswa->nama_bank ?? '-' }}<br><span class="text-xs text-slate-500">{{ $p->mahasiswa->rekening_tersamar }}</span></td>
                    <td class="px-3 py-2">@if ($p->statusAkademikTerakhir)<x-status-badge :s="$p->statusAkademikTerakhir->status" />@else<span class="text-xs text-slate-400">belum dipantau</span>@endif</td>
                    <td class="px-3 py-2">@if ($b['layak'])<span class="text-xs font-semibold text-emerald-700">Layak</span>@else<span class="text-xs text-rose-700">{{ $b['alasan'] }}</span>@endif</td>
                    <td class="px-3 py-2">
                        @if ($y)<x-status-badge :s="$y->status" /><br><span class="text-xs text-slate-500">{{ \App\Support\Kode::rupiah($y->nominal) }} &middot; {{ $y->no_ref_keuangan ?? '-' }}</span>
                        @else<span class="text-xs text-slate-400">belum disalurkan</span>@endif
                    </td>
                </tr>
            @endforeach
            @if ($baris->isEmpty())<tr><td colspan="5" class="px-3 py-6 text-center text-slate-400">Tidak ada penerima aktif.</td></tr>@endif
            </tbody>
        </table>
    </div>
@else
    <p class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-slate-400">Pilih skema yang sudah memiliki penerima.</p>
@endif
@endsection
