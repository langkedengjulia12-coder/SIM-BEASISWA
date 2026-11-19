<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - SIM-BEASISWA UKRI</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-50 font-sans text-slate-800 antialiased">
<div class="flex min-h-screen w-full">

    <div class="relative hidden w-1/2 flex-col justify-between overflow-hidden bg-gradient-to-br from-red-700 to-red-900 p-12 text-white lg:flex">
        <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute -bottom-32 -left-20 h-96 w-96 rounded-full bg-white/5"></div>
        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-2 bg-white"></div>

        <div class="relative flex items-center gap-3">
            <img src="{{ asset('images/logo-ukri.png') }}" alt="Logo UKRI" class="h-14 w-auto shrink-0 rounded-full bg-white object-contain ring-4 ring-white/30">
            <div class="leading-tight">
                <p class="text-lg font-bold">SIM-BEASISWA</p>
                <p class="text-sm text-red-100">Universitas Kebangsaan Republik Indonesia</p>
            </div>
        </div>

        <div class="relative max-w-md">
            <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-white ring-1 ring-white/30">Portal Resmi Beasiswa</span>
            <h1 class="mt-6 text-4xl font-extrabold leading-tight">Integrasi Sistem Kemitraan &amp; Pengelolaan Beasiswa</h1>
            <p class="mt-4 text-base leading-relaxed text-red-100">
                Kelola mitra, kontrak, skema, seleksi, penyaluran dana, hingga pelaporan beasiswa dalam satu platform terpadu, transparan, dan akuntabel.
            </p>
            <ul class="mt-8 space-y-2 text-sm text-red-50">
                <li>&#10003; Manajemen kemitraan &amp; donor</li>
                <li>&#10003; Pendaftaran &amp; seleksi terintegrasi</li>
                <li>&#10003; Penyaluran dana, pelaporan &amp; audit trail</li>
            </ul>
        </div>

        <p class="relative text-xs text-red-100">&copy; {{ date('Y') }} Universitas Kebangsaan Republik Indonesia</p>
    </div>

    <div class="flex w-full flex-col items-center justify-center bg-white px-6 py-10 sm:px-12 lg:w-1/2">
        <div class="w-full max-w-md">
            <div class="mb-8 flex items-center gap-3 lg:hidden">
                <img src="{{ asset('images/logo-ukri.png') }}" alt="Logo UKRI" class="h-11 w-auto shrink-0 object-contain">
                <div class="leading-tight">
                    <p class="font-bold text-red-700">SIM-BEASISWA</p>
                    <p class="text-xs text-slate-500">Universitas UKRI</p>
                </div>
            </div>

            <h2 class="text-2xl font-bold text-slate-900">Selamat Datang</h2>
            <p class="mt-1 text-sm text-slate-500">Masuk dengan username dan password akun Anda.</p>
            <div class="mt-3 h-1 w-14 rounded bg-red-600"></div>

            @if (session('error'))
                <div class="mt-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                @csrf
                <div>
                    <label for="username" class="mb-1.5 block text-sm font-medium text-slate-700">Username</label>
                    <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus autocomplete="username"
                           placeholder="Masukkan username Anda"
                           class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-red-600 focus:ring-1 focus:ring-red-600 @error('username') border-red-500 @enderror">
                    @error('username')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div x-data="{ lihat: false }">
                    <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Password</label>
                    <div class="relative">
                        <input :type="lihat ? 'text' : 'password'" id="password" name="password" required autocomplete="current-password"
                               placeholder="Masukkan password Anda"
                               class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-3 pr-16 text-sm text-slate-900 placeholder-slate-400 focus:border-red-600 focus:ring-1 focus:ring-red-600 @error('password') border-red-500 @enderror">
                        <button type="button" @click="lihat = !lihat" class="absolute inset-y-0 right-0 px-3 text-xs font-semibold text-slate-500 hover:text-red-700" x-text="lihat ? 'Sembunyi' : 'Lihat'">Lihat</button>
                    </div>
                    @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <button type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-red-700 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2">
                    Masuk ke Sistem Beasiswa
                </button>
                <p class="text-center text-xs text-slate-500">Akun dibuat oleh Admin WR3. Lupa password? Hubungi Unit Beasiswa.</p>
            </form>
        </div>
    </div>
</div>
</body>
</html>
