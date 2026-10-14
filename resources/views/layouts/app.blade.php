<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - SIM-BEASISWA UKRI</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full bg-slate-50 font-sans text-slate-800 antialiased">
@php
    // Menu utama (6 modul) - icon berupa path SVG (Heroicons outline)
    $menus = [
        ['route' => 'beasiswa.dashboard',     'label' => 'Dashboard Eksekutif',       'icon' => 'M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z'],
        ['route' => 'beasiswa.partners',      'label' => 'Manajemen Kemitraan & MoU', 'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m10-6.13a4 4 0 11-8 0 4 4 0 018 0zM21 8a3 3 0 11-6 0 3 3 0 016 0zM9 8a3 3 0 11-6 0 3 3 0 016 0z'],
        ['route' => 'beasiswa.schemes',       'label' => 'Skema Beasiswa',            'icon' => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.42A12 12 0 0112 21a12 12 0 01-6.16-10.42L12 14z'],
        ['route' => 'beasiswa.applications',  'label' => 'Seleksi & Pendaftaran',     'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['route' => 'beasiswa.disbursements', 'label' => 'Penyaluran & Rekonsiliasi', 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
        ['route' => 'beasiswa.reports',       'label' => 'Pelaporan & Audit',         'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
    ];
    $userName = Auth::user()?->name ?? 'Admin Beasiswa';
    $userRole = Auth::user()?->role ?? 'Administrator';
    $initials = strtoupper(mb_substr($userName, 0, 1));
@endphp

<div x-data="{ sidebarOpen: false }" class="flex min-h-screen w-full">

    {{-- Overlay (mobile) --}}
    <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
         class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden" style="display:none"></div>

    {{-- ================= SIDEBAR ================= --}}
    {{-- Mobile: off-canvas (fixed). Desktop (lg): kolom tetap w-64 di sisi kiri, sticky setinggi layar. --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-40 flex w-64 shrink-0 flex-col bg-[#B30000] text-red-50 shadow-xl transition-transform duration-200 lg:sticky lg:top-0 lg:z-20 lg:h-screen lg:shadow-none"
           style="background-color:#B30000">

        {{-- Brand --}}
        <div class="flex h-16 shrink-0 items-center gap-3 border-b border-white/10 px-5">
            <img src="{{ asset('images/logo-ukri.png') }}" alt="Logo UKRI" class="h-10 w-auto shrink-0 rounded-full bg-white object-contain">
            <div class="leading-tight">
                <p class="text-base font-bold text-white">SIM-BEASISWA</p>
                <p class="text-xs font-medium text-red-100">Universitas UKRI</p>
            </div>
        </div>

        {{-- Menu --}}
        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
            <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-red-200">Menu Utama</p>
            @foreach ($menus as $menu)
                @php $active = request()->routeIs($menu['route']); @endphp
                <a href="{{ route($menu['route']) }}"
                   class="group flex items-center gap-3 rounded-lg border-l-4 px-3 py-2.5 text-sm font-medium transition
                          {{ $active ? 'border-white bg-red-900 text-white shadow-inner' : 'border-transparent text-red-100 hover:bg-red-900/60 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0 {{ $active ? 'text-white' : 'text-red-200 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $menu['icon'] }}" />
                    </svg>
                    <span class="leading-snug">{{ $menu['label'] }}</span>
                </a>
            @endforeach
        </nav>

        {{-- Footer sidebar --}}
        <div class="border-t border-white/10 px-5 py-3 text-xs text-red-200">
            &copy; {{ date('Y') }} UKRI &middot; v1.0 (Preview UI)
        </div>
    </aside>

    {{-- ================= KONTEN ================= --}}
    <div class="flex min-h-screen min-w-0 flex-1 flex-col bg-slate-50">

        {{-- TOPBAR --}}
        <header class="sticky top-0 z-10 flex h-16 items-center justify-between gap-3 border-b border-slate-200 bg-white/95 px-4 shadow-sm backdrop-blur sm:px-6">
            <div class="flex items-center gap-3">
                <button type="button" @click="sidebarOpen = true" class="rounded-md p-2 text-slate-600 hover:bg-slate-100 lg:hidden" aria-label="Buka menu">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <img src="{{ asset('images/logo-ukri.png') }}" alt="Logo UKRI" class="hidden h-10 w-auto object-contain sm:block">
                <div class="leading-tight">
                    <p class="text-sm font-bold text-red-700 sm:text-base">Portal SIM-BEASISWA UKRI</p>
                    <p class="hidden text-xs text-slate-500 sm:block">Sistem Informasi Manajemen Beasiswa</p>
                </div>
            </div>

            <div class="flex items-center gap-3 sm:gap-4">
                {{-- Filter Tahun Akademik --}}
                <label class="hidden items-center gap-2 md:flex">
                    <span class="text-xs font-medium text-slate-500">T.A.</span>
                    <select class="rounded-md border-slate-300 bg-slate-50 py-1.5 pl-3 pr-8 text-sm font-medium text-slate-700 focus:border-red-600 focus:ring-red-600">
                        <option selected>2026/2027 Genap</option>
                        <option>2026/2027 Ganjil</option>
                        <option>2025/2026 Genap</option>
                        <option>2025/2026 Ganjil</option>
                    </select>
                </label>

                {{-- Profile --}}
                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = !open" @click.outside="open = false"
                            class="flex items-center gap-2 rounded-full py-1 pl-1 pr-3 hover:bg-slate-100">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-red-700 text-sm font-bold text-white">{{ $initials }}</span>
                        <span class="hidden text-left leading-tight sm:block">
                            <span class="block text-sm font-semibold text-slate-800">{{ $userName }}</span>
                            <span class="block text-xs capitalize text-slate-500">{{ $userRole }}</span>
                        </span>
                        <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-transition style="display:none"
                         class="absolute right-0 mt-2 w-48 rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                        @auth
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-red-50">Profil Saya</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-rose-600 hover:bg-rose-50">Keluar</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-red-50">Masuk</a>
                        @endauth
                    </div>
                </div>
            </div>
        </header>

        {{-- MAIN --}}
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

            @hasSection('content')
                @yield('content')
            @else
                {{ $slot ?? '' }}
            @endif
        </main>

        <footer class="border-t border-slate-200 bg-white px-6 py-3 text-center text-xs text-slate-500">
            SIM-BEASISWA &middot; Universitas UKRI &middot; T.A. 2026/2027 Genap
        </footer>
    </div>
</div>

@stack('scripts')
</body>
</html>
