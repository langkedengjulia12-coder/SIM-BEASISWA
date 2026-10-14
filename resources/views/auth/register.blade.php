<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SIM-BEASISWA UKRI</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #b91c1c 0%, #7f1d1d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }

        .text-primary { color: #b91c1c !important; }
        a.text-primary:hover { color: #991b1b !important; }

        .register-container {
            max-width: 500px;
            width: 100%;
            padding: 15px;
        }

        .register-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.12);
            overflow: hidden;
        }

        .register-header {
            background: linear-gradient(135deg, #b91c1c 0%, #7f1d1d 100%);
            padding: 32px 28px;
            text-align: center;
            color: white;
        }

        .register-body {
            padding: 32px 28px 24px;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px 14px;
            border: 1px solid #e2e8f0;
        }

        .form-control:focus {
            border-color: #b91c1c;
            box-shadow: 0 0 0 0.2rem rgba(185, 28, 28, 0.15);
        }

        .btn-register {
            background: #b91c1c;
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            color: white;
        }

        .btn-register:hover {
            background: #991b1b;
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(127, 29, 29, 0.3);
            color: white;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <i class="fas fa-graduation-cap fa-3x mb-3"></i>
                <h2 class="mb-0">Daftar Akun</h2>
                <p class="mb-0">Buat akun untuk mengakses SIM-BEASISWA UKRI</p>
            </div>
            
            <div class="register-body">
                <form method="POST" action="{{ route('register.store') }}">
                    @csrf
                    
                    <!-- Name -->
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-user text-primary"></i> Nama Lengkap
                        </label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               name="name" 
                               value="{{ old('name') }}" 
                               placeholder="Masukkan nama lengkap"
                               required>
                        @error('name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    
                    <!-- Email -->
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-envelope text-primary"></i> Email
                        </label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               name="email" 
                               value="{{ old('email') }}" 
                               placeholder="Masukkan email"
                               required>
                        @error('email')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    
                    <!-- NIK -->
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-id-card text-primary"></i> NIK
                        </label>
                        <input type="text"
                               class="form-control @error('nik') is-invalid @enderror"
                               name="nik"
                               id="nik"
                               value="{{ old('nik') }}"
                               placeholder="Masukkan NIK"
                               pattern="\d{16}"
                               title="NIK harus 16 digit angka"
                               required>
                        @error('nik')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- No. HP -->
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-phone text-primary"></i> No. HP
                        </label>
                        <input type="text"
                               class="form-control @error('no_hp') is-invalid @enderror"
                               name="no_hp"
                               id="no_hp"
                               value="{{ old('no_hp') }}"
                               placeholder="Masukkan nomor handphone"
                               pattern="[0-9+\- ]{9,20}"
                               title="Masukkan nomor telepon yang valid"
                               required>
                        @error('no_hp')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    
                    <!-- Password -->
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-lock text-primary"></i> Password
                        </label>
                        <input type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               name="password" 
                               placeholder="Masukkan password"
                               required>
                        @error('password')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    
                    <!-- Confirm Password -->
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-lock text-primary"></i> Konfirmasi Password
                        </label>
                        <input type="password" 
                               class="form-control" 
                               name="password_confirmation" 
                               placeholder="Ulangi password"
                               required>
                    </div>
                    
                    <!-- Submit -->
                    <button type="submit" class="btn btn-register w-100">
                        <i class="fas fa-user-plus"></i> Daftar
                    </button>
                    
                    <!-- Login Link -->
                    <div class="text-center mt-3">
                        <p class="mb-0">Sudah punya akun? 
                            <a href="{{ route('login') }}" class="text-primary fw-bold">
                                Login Sekarang
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="text-center mt-3">
            <a href="{{ url('/') }}" class="text-white text-decoration-none">
                <i class="fas fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>
    </div>
</body>
</html>
<script>
    (function() {
        const form = document.querySelector('form');
        if (!form) return;
        form.addEventListener('submit', function(e) {
            const nik = document.getElementById('nik');
            const noHp = document.getElementById('no_hp');

            if (nik && !/^[0-9]{16}$/.test(nik.value)) {
                e.preventDefault();
                alert('NIK harus 16 digit angka.');
                nik.focus();
                return false;
            }

            if (noHp && !/^[0-9+\- ]{9,20}$/.test(noHp.value)) {
                e.preventDefault();
                alert('Nomor HP tidak valid.');
                noHp.focus();
                return false;
            }
        });
    })();
</script>