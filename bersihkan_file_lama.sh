#!/bin/sh
# Menghapus file LAMA (sistem kontrakan/Spatie) yang sudah tidak dipakai setelah overlay SKPL dipasang.
# OPSIONAL: aplikasi tetap berjalan tanpa menjalankan skrip ini.
# Jalankan dari folder root proyek:  sh bersihkan_file_lama.sh
set -e
hapus() { [ -e "$1" ] && rm -rf "$1" && echo "dihapus: $1"; return 0; }

# Controller & request lama
for f in Admin/PartnerController Admin/SchemeController ApplicationController DisbursementController PartnerController \
         ScholarshipSchemeController ProfileController Auth/AuthController Auth/BaseController Auth/LoginRegisterController \
         Mahasiswa/DashboardController; do hapus "app/Http/Controllers/$f.php"; done
hapus app/Http/Requests

# Model lama
for f in Application Disbursement Partner ScholarshipScheme; do hapus "app/Models/$f.php"; done

# Seeder & factory lama
for f in ApplicationSeeder DisbursementSeeder PartnerSeeder RoleSeeder ScholarshipSchemeSeeder UserSeeder; do hapus "database/seeders/$f.php"; done
hapus database/factories/UserFactory.php

# Migrasi lama (sudah dinetralkan menjadi stub)
for f in 2014_10_12_000000_create_users_table 2025_12_30_033625_add_role_to_users_table \
         2026_11_01_000001_create_partners_table 2026_11_01_000002_create_scholarship_schemes_table \
         2026_11_01_000003_create_applications_table 2026_11_01_000004_create_disbursements_table \
         2026_11_01_000005_add_confirmation_and_gpa_fields; do hapus "database/migrations/$f.php"; done

# View lama
hapus resources/views/beasiswa
hapus resources/views/mahasiswa
for f in confirm-password forgot-password register reset-password verify-email; do hapus "resources/views/auth/$f.blade.php"; done

# Aset build lama
hapus public/build/assets/app-DuZmAW_S.css

# Dokumen sisa proyek lain (Sistem Informasi Kontrakan)
for f in CHECKLIST_KRU DOKUMENTASI_INDEX DOKUMENTASI_KRU IMPLEMENTASI MANIFEST MULAI_DARI_SINI PRESENTASI_BACKEND_FRONTEND \
         PROGRESS_SIMK QUICK_REFERENCE_KRU README_KRU RINGKASAN SELESAI VISUAL_GUIDE; do hapus "$f.md"; done
hapus route_list.txt

echo "Selesai. Jalankan: composer dump-autoload"
