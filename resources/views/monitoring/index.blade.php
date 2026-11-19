@extends('layouts.app')
@section('title', 'Monitoring Akademik')
@section('page-title', 'Monitoring Status Akademik Penerima')
@section('page-subtitle', 'Perbarui IPS / IPK penerima aktif dan evaluasi terhadap IPK minimum skema (P-15)')

@section('content')
<form method="POST" action="{{ route('monitoring.perbarui') }}">
    @csrf
    <div class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <x-field label="Semester (contoh: 2026/1)" name="semester" :value="$semester" :keep="true" pattern="\d{4}/[12]" required class="w-48" />
        <button class="rounded-lg bg-red-700 px-5 py-2 text-sm font-semibold text-white hover:bg-red-800">Perbarui Data</button>
        <p class="text-xs text-slate-500">Isi IPS dan IPK terbaru dari data akademik. Baris yang dikosongkan dilewati.</p>
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <th class="px-3 py-2">Penerima</th><th class="px-3 py-2">Skema</th><th class="px-3 py-2">IPK Min.</th><th class="px-3 py-2">IPK Saat Ini</th><th class="px-3 py-2">Status Terakhir</th><th class="px-3 py-2">IPS Baru</th><th class="px-3 py-2">IPK Baru</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($penerima as $p)
                @php $st = $p->statusAkademikTerakhir; @endphp
                <tr>
                    <td class="px-3 py-2 font-medium">{{ $p->mahasiswa->nama_lengkap }}<br><span class="text-xs font-normal text-slate-500">{{ $p->npm }}</span></td>
                    <td class="px-3 py-2">{{ $p->skema->nama_skema }}</td>
                    <td class="px-3 py-2">{{ number_format($p->skema->ipk_minimum, 2) }}</td>
                    <td class="px-3 py-2">{{ number_format($p->mahasiswa->ipk, 2) }}</td>
                    <td class="px-3 py-2">@if ($st)<x-status-badge :s="$st->status" /><br><span class="text-xs text-slate-400">{{ $st->semester }}</span>@else<span class="text-xs text-slate-400">belum dipantau</span>@endif</td>
                    <td class="px-3 py-2"><input type="number" name="data[{{ \App\Support\Kode::kunci($p->id_penerima) }}][ips]" min="0" max="4" step="0.01" value="{{ old('data.'.\App\Support\Kode::kunci($p->id_penerima).'.ips') }}" class="w-20 rounded-lg border-slate-300 py-1 text-sm focus:border-red-600 focus:ring-red-600"></td>
                    <td class="px-3 py-2"><input type="number" name="data[{{ \App\Support\Kode::kunci($p->id_penerima) }}][ipk]" min="0" max="4" step="0.01" value="{{ old('data.'.\App\Support\Kode::kunci($p->id_penerima).'.ipk') }}" class="w-20 rounded-lg border-slate-300 py-1 text-sm focus:border-red-600 focus:ring-red-600"></td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-3 py-6 text-center text-slate-400">Belum ada penerima aktif.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</form>
@endsection
