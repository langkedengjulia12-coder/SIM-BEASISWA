@if ($hasil)
<div class="mt-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    <h2 class="font-semibold text-slate-800">{{ $hasil['judul'] }}</h2>
    <p class="text-xs text-slate-500">Periode {{ $hasil['awal']->format('d-m-Y') }} s.d. {{ $hasil['akhir']->format('d-m-Y') }} &middot; arsip laporan tersimpan.</p>
    <div class="mt-3 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                @foreach ($hasil['header'] as $i => $h)<th class="px-3 py-2 {{ $hasil['tipe'][$i] !== 'teks' ? 'text-right' : '' }}">{{ $h }}</th>@endforeach
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($hasil['rows'] as $row)
                    <tr>@foreach ($row as $i => $c)<td class="px-3 py-2 {{ $hasil['tipe'][$i] !== 'teks' ? 'text-right' : '' }}">{{ $hasil['tipe'][$i] === 'uang' ? \App\Support\Kode::rupiah($c) : $c }}</td>@endforeach</tr>
                @endforeach
            </tbody>
            <tfoot class="bg-slate-50 font-semibold">
                <tr>
                    @foreach ($hasil['header'] as $i => $h)
                        <td class="px-3 py-2 {{ $hasil['tipe'][$i] !== 'teks' ? 'text-right' : '' }}">
                            @if ($i === 0) Total
                            @elseif ($hasil['tipe'][$i] === 'uang') {{ \App\Support\Kode::rupiah(collect($hasil['rows'])->sum($i)) }}
                            @elseif ($hasil['tipe'][$i] === 'angka') {{ collect($hasil['rows'])->sum($i) }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif

<div class="mt-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    <h2 class="font-semibold text-slate-800">Arsip Laporan Terakhir</h2>
    <ul class="mt-3 divide-y divide-slate-100 text-sm">
        @forelse ($arsip as $a)
            <li class="flex items-center justify-between py-2">
                <span><span class="font-mono text-xs">{{ $a->id_laporan }}</span> &middot; {{ ucfirst($a->jenis_laporan) }} {{ $a->mitra?->nama_mitra }} &middot; {{ $a->periode_awal->format('d-m-Y') }} s.d. {{ $a->periode_akhir->format('d-m-Y') }} ({{ $a->format_file }})</span>
                <a href="{{ route('laporan.unduh', $a) }}" class="text-xs font-semibold text-sky-700 hover:underline">Unduh</a>
            </li>
        @empty
            <li class="py-2 text-slate-400">Belum ada arsip laporan.</li>
        @endforelse
    </ul>
</div>
