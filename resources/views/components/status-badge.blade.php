@props(['s'])
@php
    $hijau = 'bg-emerald-100 text-emerald-800 ring-emerald-200';
    $biru = 'bg-sky-100 text-sky-800 ring-sky-200';
    $ungu = 'bg-indigo-100 text-indigo-800 ring-indigo-200';
    $kuning = 'bg-amber-100 text-amber-800 ring-amber-200';
    $oranye = 'bg-orange-100 text-orange-800 ring-orange-200';
    $merah = 'bg-rose-100 text-rose-800 ring-rose-200';
    $abu = 'bg-slate-100 text-slate-700 ring-slate-200';
    $peta = [
        'aktif' => $hijau, 'terverifikasi' => $hijau, 'valid' => $hijau, 'tersalurkan' => $hijau, 'dikonfirmasi' => $hijau,
        'diterima' => $hijau, 'memenuhi' => $hijau, 'dibuka' => $hijau,
        'lolos_administrasi' => $biru, 'diproses' => $biru, 'diperpanjang' => $biru,
        'dinilai' => $ungu,
        'diajukan' => $kuning, 'menunggu' => $kuning,
        'cadangan' => $oranye, 'peringatan' => $oranye, 'tidak_lengkap' => $oranye,
        'ditolak' => $merah, 'gagal' => $merah, 'tidak_valid' => $merah, 'tidak_memenuhi' => $merah, 'dihentikan' => $merah,
    ];
    $kelas = $peta[$s] ?? $abu;
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset $kelas"]) }}>{{ str_replace('_', ' ', $s) }}</span>
