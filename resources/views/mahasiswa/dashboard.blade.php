<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Portal Mahasiswa - SIM-BEASISWA UKRI</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ mobileMenu: false, uploadedFile: '' }" class="min-h-screen bg-[#f7f8fa] font-sans text-slate-800 antialiased">
@php
    $studentName = $student['name'] ?? 'Dimas Pratama Yudha';
    $nim = $student['nim'] ?? '20241320084';
    $initials = collect(explode(' ', $studentName))->filter()->map(fn ($name) => mb_substr($name, 0, 1))->take(2)->join('');
    $steps = [
        ['number' => '1', 'title' => 'Pendaftaran', 'date' => '12 Mei 2026', 'note' => 'Pengajuan diterima'],
        ['number' => '2', 'title' => 'Berkas & SIAK', 'date' => '20 Mei 2026', 'note' => 'Berkas terverifikasi'],
        ['number' => '3', 'title' => 'Uji Wawancara', 'date' => '28 Mei 2026', 'note' => 'Lulus seleksi'],
        ['number' => '4', 'title' => 'SK Rektor UKRI', 'date' => '10 Juni 2026', 'note' => 'SK diterbitkan'],
        ['number' => '5', 'title' => 'Penyaluran Dana', 'date' => '24 Feb 2026', 'note' => 'Termin I tersalurkan'],
    ];
    $scholarshipCards = [
        [
            'name' => 'Beasiswa Prestasi Akademik BUMN 2026',
            'partner' => 'Mitra Konsorsium BUMN',
            'quota' => 'Sisa 16 Slot Kuota',
            'deadline' => '15 Mar 2026',
            'requirement' => 'Syarat IPK Min 3.25 Terpenuhi',
            'benefit' => 'Bebas SPP + Uang Saku',
        ],
        [
            'name' => 'Beasiswa Riset & TA PT Len Industri',
            'partner' => 'Khusus Riset & TA',
            'quota' => 'Program Riset',
            'deadline' => '30 Apr 2026',
            'requirement' => 'Proposal riset terverifikasi',
            'benefit' => 'Hibah Rp 15 Juta',
        ],
        [
            'name' => 'Beasiswa Talenta Pemprov Jabar 2026',
            'partner' => 'Pemprov Jabar',
            'quota' => 'Talenta & kepemimpinan',
            'deadline' => '20 Mar 2026',
            'requirement' => 'Rekomendasi fakultas',
            'benefit' => 'Uang pembinaan & pelatihan',
        ],
    ];
@endphp

<header class="sticky top-0 z-50 border-b border-slate-200 bg-white/95 shadow-sm backdrop-blur">
    <div class="mx-auto flex max-w-[1480px] items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
        <a href="#beranda" class="flex shrink-0 items-center gap-3">
            <img src="{{ asset('images/logo-ukri.png') }}" alt="Logo UKRI" class="h-11 w-11 rounded-full object-contain">
            <div class="hidden leading-tight sm:block">
                <p class="text-sm font-extrabold tracking-wide text-[#990000]">SIM-BEASISWA UKRI</p>
                <p class="text-[11px] font-semibold tracking-wider text-slate-500">PORTAL MAHASISWA</p>
            </div>
        </a>
        <nav class="hidden items-center gap-1 rounded-full bg-slate-100 p-1.5 text-xs font-semibold xl:flex">
            <a href="#beranda" class="rounded-full bg-[#990000] px-4 py-2.5 text-white shadow-sm">Beranda</a>
            <a href="#status" class="rounded-full px-4 py-2.5 text-slate-600 transition hover:bg-white hover:text-[#990000]">Status Beasiswa Saya</a>
            <a href="#konfirmasi" class="rounded-full px-4 py-2.5 text-slate-600 transition hover:bg-white hover:text-[#990000]">Konfirmasi Pencairan</a>
            <a href="#riwayat" class="rounded-full px-4 py-2.5 text-slate-600 transition hover:bg-white hover:text-[#990000]">Riwayat Pembayaran</a>
            <a href="#program" class="rounded-full px-4 py-2.5 text-slate-600 transition hover:bg-white hover:text-[#990000]">Pengumuman</a>
        </nav>
        <div class="flex items-center gap-2 sm:gap-3">
            <button type="button" class="relative rounded-lg p-2 text-slate-500 transition hover:bg-red-50 hover:text-[#990000]" aria-label="Notifikasi">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17H9m9-2V11a6 6 0 0 0-12 0v4l-2 2h16l-2-2Zm-5 5h-2"/></svg>
                <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-[#990000]"></span>
            </button>
            <div class="hidden border-l border-slate-200 pl-3 text-right sm:block">
                <p class="text-xs font-bold text-slate-800">{{ $studentName }}</p>
                <p class="text-[11px] text-slate-500">NIM {{ $nim }}</p>
            </div>
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#990000] text-xs font-bold uppercase text-white">{{ $initials }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-lg p-2 text-slate-500 transition hover:bg-red-50 hover:text-[#990000]" title="Keluar" aria-label="Keluar">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4m-5-4 4-5-4-5m4 5H3"/></svg>
                </button>
            </form>
            <button type="button" @click="mobileMenu = !mobileMenu" class="rounded-lg p-2 text-slate-600 xl:hidden" aria-label="Buka navigasi">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
    </div>
    <nav x-show="mobileMenu" x-transition class="border-t border-slate-100 px-4 py-2 xl:hidden" style="display:none">
        <div class="flex gap-1 overflow-x-auto text-xs font-semibold">
            <a href="#beranda" class="whitespace-nowrap rounded-lg bg-red-50 px-3 py-2 text-[#990000]">Beranda</a>
            <a href="#status" class="whitespace-nowrap rounded-lg px-3 py-2 text-slate-600">Status Beasiswa</a>
            <a href="#konfirmasi" class="whitespace-nowrap rounded-lg px-3 py-2 text-slate-600">Konfirmasi</a>
            <a href="#riwayat" class="whitespace-nowrap rounded-lg px-3 py-2 text-slate-600">Riwayat</a>
            <a href="#program" class="whitespace-nowrap rounded-lg px-3 py-2 text-slate-600">Pengumuman</a>
        </div>
    </nav>
</header>

<main class="mx-auto max-w-[1480px] space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <section id="beranda" class="scroll-mt-24 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
        <div class="flex flex-col justify-between gap-6 lg:flex-row lg:items-center">
            <div>
                <p class="text-xs font-extrabold tracking-wider text-[#990000]">SIM-BEASISWA MAHASISWA • T.A. 2026/2027 GENAP</p>
                <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Selamat Datang, {{ $studentName }} <span class="font-semibold text-slate-500">(NIM: {{ $nim }})</span></h1>
                <p class="mt-3 text-sm text-slate-600">Program Studi {{ $student['program'] ?? 'S1 Sistem Informasi' }} — {{ $student['faculty'] ?? 'Fakultas Ilmu Komunikasi & Informatika (FIKONSI)' }} UKRI <span class="hidden sm:inline">•</span> {{ $student['semester'] ?? 'Semester 4 (T.A. 2026/2027 Genap)' }}</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-bold text-emerald-800">Mahasiswa Aktif SIAK</span>
                    <span class="rounded-full bg-amber-100 px-3 py-1.5 text-xs font-bold text-amber-800">IPK Terakhir: {{ $student['gpa'] ?? '3.82' }} (Terverifikasi SIAK)</span>
                    <span class="rounded-full bg-sky-100 px-3 py-1.5 text-xs font-bold text-sky-800">Validasi BAAK Lolos</span>
                </div>
            </div>
            <div class="flex shrink-0 flex-col gap-2 lg:w-64">
                <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#990000]">Semester Berjalan</p>
                    <p class="mt-1 text-sm font-bold text-slate-900">T.A. 2026/2027 Genap</p>
                </div>
                <a href="{{ route('mahasiswa.guide.download') }}" class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-red-200 hover:bg-red-50 hover:text-[#990000]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L8 8m4-4 4 4M5 15v3a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-3"/></svg>
                    Panduan &amp; Regulasi SK Beasiswa
                </a>
            </div>
        </div>
    </section>

    <section id="status" class="scroll-mt-24 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="bg-[#990000] p-5 text-white sm:px-7">
            <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
                <div><p class="text-xs font-bold tracking-[0.18em] text-red-100">STATUS PENERIMA</p><h2 class="mt-1 text-xl font-extrabold">PENERIMA AKTIF — Beasiswa Yayasan Budhi Dharma 2026</h2></div>
                <div class="flex flex-wrap items-center gap-3 text-sm"><span class="text-red-100">SK Rektor No. 104/UKRI/BEAS/2026</span><span class="rounded-full bg-emerald-400/20 px-3 py-1.5 font-bold text-emerald-100">Termin 1 Telah Disalurkan</span></div>
            </div>
        </div>
        <div class="grid gap-4 bg-slate-50 p-5 sm:grid-cols-2 sm:p-7 lg:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-5"><div class="flex items-center justify-between"><p class="text-xs font-semibold text-slate-500">Nilai Beasiswa / Periode</p><span class="text-[#990000]">◈</span></div><p class="mt-2 text-xl font-extrabold text-slate-900">Rp 10.000.000</p><p class="mt-1 text-xs text-slate-500">Rp 5.000.000 / Semester • 2 Termin</p></div>
            <div class="rounded-xl border border-slate-200 bg-white p-5"><p class="text-xs font-semibold text-slate-500">Rekening Pencairan H2H</p><p class="mt-2 text-sm font-extrabold text-slate-900">Bank Mandiri (Host-to-Host)</p><p class="mt-1 text-xs text-slate-500">131-00-990011-4 a.n. {{ $studentName }}</p></div>
            <div class="rounded-xl border border-slate-200 bg-white p-5"><p class="text-xs font-semibold text-slate-500">Komponen Manfaat</p><p class="mt-2 text-sm font-extrabold text-slate-900">100% Pembebasan BPP</p><p class="mt-1 text-xs text-slate-500">Uang Saku &amp; Biaya Hidup Termin I</p></div>
            <div class="rounded-xl border border-slate-200 bg-white p-5"><p class="text-xs font-semibold text-slate-500">Periode Kontrak &amp; Evaluasi</p><p class="mt-2 text-sm font-extrabold text-slate-900">Genap 2026 — Ganjil 2027</p><p class="mt-1 text-xs text-slate-500">Syarat Evaluasi: IPK Minimum 3.25</p></div>
        </div>
        <div id="riwayat" class="mx-5 mb-5 flex items-start gap-2 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-900 sm:mx-7 sm:mb-7"><svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m9 12.75 2.25 2.25L15 10.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg><span><strong>Status Transaksi Terakhir:</strong> Dana Termin I sebesar Rp 5.000.000 berhasil masuk ke rekening Bank Mandiri pada 24 Feb 2026, 09:15 WIB via Host-to-Host (H2H).</span></div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
        <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-end"><div><p class="text-xs font-extrabold tracking-wider text-[#990000]">PEMANTAUAN KEPATUHAN SIAK &amp; WR III</p><h2 class="mt-1 text-xl font-extrabold text-slate-900">Rangkaian Tahapan Penyaluran Beasiswa</h2></div><span class="w-fit rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-bold text-emerald-800">Progres Termin 1: 100% Selesai</span></div>
        <div class="relative mt-8 grid gap-5 md:grid-cols-5 md:gap-2">
            <div class="absolute left-[10%] right-[10%] top-5 hidden h-1 bg-emerald-200 md:block"></div>
            @foreach ($steps as $step)
                <div class="relative text-center"><span class="relative mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-emerald-600 text-sm font-bold text-white ring-4 ring-white">✓</span><p class="mt-3 text-sm font-bold text-slate-900">{{ $step['number'] }}. {{ $step['title'] }}</p><p class="mt-1 text-xs font-semibold text-[#990000]">{{ $step['date'] }}</p><p class="mt-1 text-xs text-slate-500">{{ $step['note'] }}</p></div>
            @endforeach
        </div>
        <div class="mt-6 rounded-xl border border-red-100 bg-red-50 p-4 text-sm text-red-900"><strong>Tahap Lanjutan Berikutnya:</strong> Evaluasi KHS Semester Genap &amp; Pencairan Termin II (Rp 5.000.000) dijadwalkan pada Juli 2026.</div>
    </section>

    <section id="konfirmasi" class="scroll-mt-24 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
        <div class="flex flex-col justify-between gap-3 border-b border-slate-100 pb-5 sm:flex-row sm:items-start"><div><p class="text-xs font-extrabold tracking-wider text-[#990000]">PERLU TINDAKAN MAHASISWA</p><h2 class="mt-1 text-xl font-extrabold text-slate-900">Validasi Tanda Terima Termin I</h2><p class="mt-2 max-w-3xl text-sm text-slate-600">Sesuai regulasi Biro Kemahasiswaan &amp; Kerjasama WR III UKRI, mahasiswa wajib mengunggah bukti mutasi dana untuk memvalidasi penerimaan beasiswa.</p></div><span class="w-fit rounded-lg bg-orange-100 px-3 py-2 text-xs font-bold text-orange-800">Batas Waktu: 05 Maret 2026<br><span class="font-medium">Sisa 8 Hari</span></span></div>
        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <form method="POST" action="{{ route('mahasiswa.confirmation.store') }}" enctype="multipart/form-data">@csrf<div><p class="text-sm font-bold text-slate-900">Bukti Mutasi Rekening / Screenshot M-Banking</p><label class="mt-3 flex min-h-48 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-red-200 bg-red-50/40 p-6 text-center transition hover:border-[#990000] hover:bg-red-50"><svg class="h-10 w-10 text-[#990000]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L8 8m4-4 4 4M5 15v3a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-3"/></svg><span class="mt-3 text-sm font-bold text-slate-700" x-text="uploadedFile || 'Drag & drop atau klik untuk unggah'"></span><span class="mt-1 text-xs text-slate-500">Format PDF, JPG, PNG • Maksimal 2MB</span><input name="mutation_file" required type="file" class="sr-only" accept=".pdf,.jpg,.jpeg,.png" @change="uploadedFile = $event.target.files[0]?.name || ''"></label></div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-5"><h3 class="text-sm font-extrabold text-slate-900">Ringkasan Validasi H2H</h3><dl class="mt-4 space-y-3 text-sm"><div class="flex justify-between gap-4"><dt class="text-slate-500">Nominal Tercatat</dt><dd class="font-bold text-slate-900">Rp 5.000.000</dd></div><div class="flex justify-between gap-4"><dt class="text-slate-500">Bank Penerima</dt><dd class="font-bold text-slate-900">Bank Mandiri</dd></div><div class="flex justify-between gap-4"><dt class="text-slate-500">ID Referensi H2H</dt><dd class="font-mono text-xs font-bold text-slate-900">H2H-UKRI-240226-001</dd></div></dl><label class="mt-5 flex gap-2 text-xs leading-5 text-slate-600"><input name="confirmation" value="1" required type="checkbox" class="mt-1 rounded border-slate-300 text-[#990000] focus:ring-[#990000]"> Saya menyatakan telah menerima dana Termin I sesuai nominal yang tercatat.</label><button type="submit" class="mt-5 w-full rounded-xl bg-[#990000] px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#760000]">Konfirmasi Saya Telah Menerima Dana Rp 5.000.000</button></div></form>
        </div>
    </section>

    <section id="program" class="scroll-mt-24 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
        <div class="flex items-end justify-between gap-3"><div><p class="text-xs font-extrabold tracking-wider text-[#990000]">PELUANG UNTUK ANDA</p><h2 class="mt-1 text-xl font-extrabold text-slate-900">Program &amp; Skema Beasiswa Tersedia</h2></div><span class="hidden text-sm font-semibold text-slate-500 sm:block">3 program aktif</span></div>
        <div class="mt-5 grid gap-4 md:grid-cols-3">
            @foreach ($scholarshipCards as $scholarship)
                @php($scheme = (object) $scholarship)
                <article class="flex flex-col rounded-xl border border-slate-200 p-5 transition hover:-translate-y-0.5 hover:border-red-200 hover:shadow-md"><div class="flex items-start justify-between gap-2"><span class="rounded-full bg-red-50 px-2.5 py-1 text-[11px] font-bold text-[#990000]">{{ $scholarship['quota'] }}</span><span class="text-right text-[11px] font-bold text-slate-500">Periode<br>{{ $scholarship['deadline'] }}</span></div><h3 class="mt-4 text-base font-extrabold text-slate-900">{{ $scholarship['name'] }}</h3><p class="mt-2 text-xs text-slate-500">{{ $scholarship['partner'] }}</p><p class="mt-3 text-xs font-semibold text-emerald-700">{{ $scholarship['requirement'] }}</p><p class="mt-2 text-sm font-bold text-[#990000]">{{ $scholarship['benefit'] }}</p><form method="POST" action="{{ route('mahasiswa.daftar', $scheme->id ?? 1) }}" class="mt-5">@csrf<button type="submit" class="w-full rounded-lg bg-[#990000] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#760000]">Daftar Sekarang</button></form></article>
            @endforeach
        </div>
    </section>
</main>

<footer class="border-t border-slate-200 bg-white">
    <div class="mx-auto max-w-[1480px] px-4 py-7 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-5 rounded-2xl bg-[#990000] p-5 text-white sm:p-6 lg:flex-row lg:items-center"><div><p class="text-lg font-extrabold">Pusat Bantuan &amp; Layanan Advokasi Beasiswa UKRI</p><p class="mt-1 text-sm text-red-100">Gedung Rektorat Lt. 2, Biro Kemahasiswaan &amp; Kerjasama WR III • Senin–Jumat, 08.00–16.00 WIB</p></div><div class="flex flex-wrap gap-2"><a href="https://wa.me/" class="rounded-lg bg-white px-4 py-2 text-sm font-bold text-[#990000]">WhatsApp Advokasi</a><a href="mailto:beasiswa@ukri.ac.id" class="rounded-lg border border-white/40 px-4 py-2 text-sm font-bold text-white">beasiswa@ukri.ac.id</a></div></div>
        <p class="mt-6 text-center text-xs text-slate-500">&copy; {{ date('Y') }} Direktorat Kemahasiswaan &amp; Kerjasama Universitas Kebangsaan Republik Indonesia.</p>
    </div>
</footer>
</body>
</html>
