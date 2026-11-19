<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') - SIM-BEASISWA UKRI</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full bg-slate-50 font-sans text-slate-800 antialiased">
@php
    $user = Auth::user();
    $role = $user->role;

    // [route, label, path ikon, pola route aktif]
    $i = [
        'home' => 'M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z',
        'org' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m10-6.13a4 4 0 11-8 0 4 4 0 018 0zM21 8a3 3 0 11-6 0 3 3 0 016 0zM9 8a3 3 0 11-6 0 3 3 0 016 0z',
        'doc' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'cap' => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.42A12 12 0 0112 21a12 12 0 01-6.16-10.42L12 14z',
        'check' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'cash' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
        'chart' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'down' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4',
        'user' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        'users' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197',
        'eye' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
    ];

    if ($user->isStaf()) {
        $menus = [
            ['Umum', [
                $user->isAdmin()
                    ? ['dashboard.admin', 'Dashboard Admin WR3', $i['home'], ['dashboard.admin']]
                    : ['beranda', 'Beranda', $i['home'], ['beranda']],
            ]],
            ['Kemitraan & Donor', [
                ['mitra.index', 'Data Mitra & Donor', $i['org'], ['mitra.*']],
                ['kontrak.index', 'Kontrak Kerja Sama', $i['doc'], ['kontrak.*']],
                ['komitmen.index', 'Komitmen Dana', $i['cash'], ['komitmen.*']],
            ]],
            ['Beasiswa', [
                ['skema.index', 'Skema Beasiswa', $i['cap'], ['skema.*']],
                ['verifikasi.index', 'Verifikasi Berkas', $i['check'], ['verifikasi.*']],
                ['penilaian.index', 'Penilaian & Ranking', $i['chart'], ['penilaian.*']],
                ['penetapan.index', 'Penetapan & Pengumuman', $i['users'], ['penetapan.*']],
                ['penyaluran.index', 'Penyaluran Dana', $i['cash'], ['penyaluran.index', 'penyaluran.salurkan']],
            ]],
            ['Pelaporan', [
                ['laporan.index', 'Laporan Realisasi', $i['chart'], ['laporan.*']],
                ['ekspor.index', 'Ekspor Data', $i['down'], ['ekspor.*']],
                ['monitoring.index', 'Monitoring Akademik', $i['eye'], ['monitoring.*']],
            ]],
        ];
        if ($user->isAdmin()) {
            $menus[] = ['Administrasi', [['pengguna.index', 'Kelola Pengguna', $i['user'], ['pengguna.*']]]];
        }
    } elseif ($user->isMitra()) {
        $menus = [['Portal Mitra', [
            ['laporan.index', 'Laporan Penyaluran', $i['chart'], ['laporan.*']],
            ['komitmen.index', 'Komitmen Dana', $i['cash'], ['komitmen.*']],
        ]]];
    } else {
        $menus = [['Mahasiswa', [
            ['pendaftaran.status', 'Status Pendaftaran', $i['check'], ['pendaftaran.status']],
            ['pendaftaran.form', 'Daftar Beasiswa', $i['cap'], ['pendaftaran.form']],
            ['penyaluran.saya', 'Konfirmasi Dana', $i['cash'], ['penyaluran.saya']],
            ['profil.edit', 'Profil & Rekening', $i['user'], ['profil.*']],
        ]]];
    }

    $belumDibaca = \App\Models\Notifikasi::where('id_pengguna', $user->id_pengguna)->where('dibaca', false)->count();
    $inisial = strtoupper(mb_substr($user->nama, 0, 1));
@endphp

<div x-data="{ sidebarOpen: false }" class="flex min-h-screen w-full">

    <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
         class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden" style="display:none"></div>

    {{-- SIDEBAR --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-40 flex w-64 shrink-0 flex-col bg-[#B30000] text-red-50 shadow-xl transition-transform duration-200 lg:sticky lg:top-0 lg:z-20 lg:h-screen lg:shadow-none"
           style="background-color:#B30000">
        <div class="flex h-16 shrink-0 items-center gap-3 border-b border-white/10 px-5">
            <img src="{{ asset('images/logo-ukri.png') }}" alt="Logo UKRI" class="h-10 w-auto shrink-0 rounded-full bg-white object-contain">
            <div class="leading-tight">
                <p class="text-base font-bold text-white">SIM-BEASISWA</p>
                <p class="text-xs font-medium text-red-100">Universitas UKRI</p>
            </div>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
            @foreach ($menus as [$grup, $item])
                <p class="px-3 pb-1 pt-3 text-[11px] font-semibold uppercase tracking-wider text-red-200">{{ $grup }}</p>
                @foreach ($item as [$rute, $label, $ikon, $pola])
                    @php $aktif = request()->routeIs(...$pola); @endphp
                    <a href="{{ route($rute) }}"
                       class="group flex items-center gap-3 rounded-lg border-l-4 px-3 py-2 text-sm font-medium transition
                              {{ $aktif ? 'border-white bg-red-900 text-white shadow-inner' : 'border-transparent text-red-100 hover:bg-red-900/60 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0 {{ $aktif ? 'text-white' : 'text-red-200 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon }}" />
                        </svg>
                        <span class="leading-snug">{{ $label }}</span>
                    </a>
                @endforeach
            @endforeach
        </nav>

        <div class="border-t border-white/10 px-5 py-3 text-xs text-red-200">&copy; {{ date('Y') }} UKRI &middot; SIKB v1.0</div>
    </aside>

    {{-- KONTEN --}}
    <div class="flex min-h-screen min-w-0 flex-1 flex-col bg-slate-50">
        <header class="sticky top-0 z-10 flex h-16 items-center justify-between gap-3 border-b border-slate-200 bg-white/95 px-4 shadow-sm backdrop-blur sm:px-6">
            <div class="flex items-center gap-3">
                <button type="button" @click="sidebarOpen = true" class="rounded-md p-2 text-slate-600 hover:bg-slate-100 lg:hidden" aria-label="Buka menu">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="leading-tight">
                    <p class="text-sm font-bold text-red-700 sm:text-base">Portal SIM-BEASISWA UKRI</p>
                    <p class="hidden text-xs text-slate-500 sm:block">Sistem Informasi Kemitraan &amp; Beasiswa</p>
                </div>
            </div>

            <div class="flex items-center gap-3 sm:gap-4">
                <a href="{{ route('notifikasi.index') }}" class="relative rounded-full p-2 text-slate-600 hover:bg-slate-100" aria-label="Notifikasi">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @if ($belumDibaca > 0)
                        <span class="absolute -right-0.5 -top-0.5 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-600 px-1 text-[11px] font-bold text-white">{{ $belumDibaca > 99 ? '99+' : $belumDibaca }}</span>
                    @endif
                </a>

                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = !open" @click.outside="open = false"
                            class="flex items-center gap-2 rounded-full py-1 pl-1 pr-3 hover:bg-slate-100">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-red-700 text-sm font-bold text-white">{{ $inisial }}</span>
                        <span class="hidden text-left leading-tight sm:block">
                            <span class="block text-sm font-semibold text-slate-800">{{ $user->nama }}</span>
                            <span class="block text-xs text-slate-500">{{ $user->label_role }}</span>
                        </span>
                    </button>
                    <div x-show="open" x-transition style="display:none"
                         class="absolute right-0 mt-2 w-48 rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-rose-600 hover:bg-rose-50">Keluar</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 bg-slate-50 p-4 sm:p-6 lg:p-8">
            @hasSection('page-title')
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-red-700">@yield('page-title')</h1>
                    @hasSection('page-subtitle')
                        <p class="mt-1 text-sm text-slate-500">@yield('page-subtitle')</p>
                    @endif
                    <div class="mt-3 h-1 w-16 rounded bg-red-600"></div>
                </div>
            @endif

            @if (session('success'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
            @endif
            @if (session('dilewati') && count(session('dilewati')))
                <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    <p class="font-semibold">Penerima yang dilewati / gagal:</p>
                    <ul class="mt-1 list-disc space-y-0.5 pl-5">@foreach (session('dilewati') as $d)<li>{{ $d }}</li>@endforeach</ul>
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    <p class="font-semibold">Periksa kembali isian Anda:</p>
                    <ul class="mt-1 list-disc space-y-0.5 pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="border-t border-slate-200 bg-white px-6 py-3 text-center text-xs text-slate-500">
            SIM-BEASISWA &middot; Universitas Kebangsaan Republik Indonesia
        </footer>
    </div>
</div>
@stack('scripts')
</body>
</html>
