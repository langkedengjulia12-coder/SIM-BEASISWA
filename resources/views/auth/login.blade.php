<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - SIM-BEASISWA UKRI</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-50 font-sans text-slate-800 antialiased">
<div class="flex min-h-screen w-full">

    {{-- ================= HERO BANNER (kiri) ================= --}}
    <div class="relative hidden w-1/2 flex-col justify-between overflow-hidden bg-gradient-to-br from-red-700 to-red-900 p-12 text-white lg:flex">
        {{-- Ornamen dekoratif --}}
        <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute -bottom-32 -left-20 h-96 w-96 rounded-full bg-white/5"></div>
        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-2 bg-white"></div>

        <div class="relative flex items-center gap-3">
            <img src="{{ asset('images/logo-ukri.png') }}" alt="Logo UKRI" class="h-14 w-auto shrink-0 rounded-full bg-white object-contain ring-4 ring-white/30">
            <div class="leading-tight">
                <p class="text-lg font-bold">SIM-BEASISWA</p>
                <p class="text-sm text-red-100">Universitas UKRI</p>
            </div>
        </div>

        <div class="relative max-w-md">
            <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-white ring-1 ring-white/30">
                <i class="fas fa-graduation-cap"></i> Portal Resmi Beasiswa
            </span>
            <h1 class="mt-6 text-4xl font-extrabold leading-tight">Sistem Informasi Manajemen Beasiswa</h1>
            <p class="mt-4 text-base leading-relaxed text-red-100">
                Kelola kemitraan, skema, seleksi, penyaluran dana, hingga pelaporan beasiswa dalam satu platform terpadu, transparan, dan akuntabel.
            </p>

            <ul class="mt-8 space-y-3 text-sm text-red-50">
                <li class="flex items-center gap-3"><span class="flex h-7 w-7 items-center justify-center rounded-full bg-white text-red-700"><i class="fas fa-check text-xs"></i></span> Manajemen kemitraan &amp; MoU</li>
                <li class="flex items-center gap-3"><span class="flex h-7 w-7 items-center justify-center rounded-full bg-white text-red-700"><i class="fas fa-check text-xs"></i></span> Seleksi &amp; pendaftaran terintegrasi</li>
                <li class="flex items-center gap-3"><span class="flex h-7 w-7 items-center justify-center rounded-full bg-white text-red-700"><i class="fas fa-check text-xs"></i></span> Penyaluran, rekonsiliasi &amp; audit trail</li>
            </ul>
        </div>

        <p class="relative text-xs text-red-100">&copy; {{ date('Y') }} Universitas UKRI &middot; SIM-BEASISWA</p>
    </div>

    {{-- ================= FORM LOGIN (kanan) ================= --}}
    <div class="flex w-full flex-col items-center justify-center bg-white px-6 py-10 sm:px-12 lg:w-1/2">
        <div class="w-full max-w-md">

            {{-- Brand (mobile) --}}
            <div class="mb-8 flex items-center gap-3 lg:hidden">
                <img src="{{ asset('images/logo-ukri.png') }}" alt="Logo UKRI" class="h-11 w-auto shrink-0 object-contain">
                <div class="leading-tight">
                    <p class="font-bold text-red-700">SIM-BEASISWA</p>
                    <p class="text-xs text-slate-500">Universitas UKRI</p>
                </div>
            </div>

            <h2 class="text-2xl font-bold text-slate-900">Selamat Datang</h2>
            <p class="mt-1 text-sm text-slate-500">Masuk untuk mengakses Sistem Informasi Manajemen Beasiswa.</p>
            <div class="mt-3 h-1 w-14 rounded bg-red-600"></div>

            @if (session('status'))
                <div class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                @csrf

                <!-- Email -->
                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-red-600">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               placeholder="Masukkan email Anda" required autofocus
                               class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3 text-sm text-slate-900 placeholder-slate-400 focus:border-red-600 focus:ring-1 focus:ring-red-600 @error('email') border-red-500 @enderror">
                    </div>
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Password</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-red-600">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" id="password" name="password"
                               placeholder="Masukkan password Anda" required
                               class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-11 text-sm text-slate-900 placeholder-slate-400 focus:border-red-600 focus:ring-1 focus:ring-red-600 @error('password') border-red-500 @enderror">
                        <button type="button" onclick="togglePassword()" aria-label="Tampilkan password"
                                class="absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-500 hover:text-red-700">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember + Forgot -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" id="remember" name="remember"
                               class="h-4 w-4 rounded border-slate-300 text-red-700 focus:ring-red-600">
                        Ingat Saya
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm font-medium text-red-700 hover:text-red-800 hover:underline">Lupa Password?</a>
                    @endif
                </div>

                <!-- Submit -->
                <button type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-red-700 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2">
                    <i class="fas fa-sign-in-alt"></i> Masuk ke Sistem Beasiswa
                </button>

                <!-- Register -->
                <p class="text-center text-sm text-slate-600">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="font-semibold text-red-700 hover:text-red-800 hover:underline">Daftar Sekarang</a>
                </p>
            </form>

            <div class="mt-8 border-t border-slate-200 pt-5 text-center">
                <a href="{{ url('/') }}" class="text-sm font-medium text-slate-500 hover:text-red-700">
                    <i class="fas fa-arrow-left"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const password = document.getElementById('password');
        const icon = document.getElementById('toggleIcon');

        if (password.type === 'password') {
            password.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            password.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
</body>
</html>

