@echo off
REM Menghapus file LAMA (sistem kontrakan/Spatie) setelah overlay SKPL dipasang. OPSIONAL.
REM Jalankan dari folder root proyek: bersihkan_file_lama.bat
for %%F in (Admin\PartnerController Admin\SchemeController ApplicationController DisbursementController PartnerController ScholarshipSchemeController ProfileController Auth\AuthController Auth\BaseController Auth\LoginRegisterController Mahasiswa\DashboardController) do if exist "app\Http\Controllers\%%F.php" del /q "app\Http\Controllers\%%F.php"
if exist app\Http\Requests rmdir /s /q app\Http\Requests
for %%F in (Application Disbursement Partner ScholarshipScheme) do if exist "app\Models\%%F.php" del /q "app\Models\%%F.php"
for %%F in (ApplicationSeeder DisbursementSeeder PartnerSeeder RoleSeeder ScholarshipSchemeSeeder UserSeeder) do if exist "database\seeders\%%F.php" del /q "database\seeders\%%F.php"
if exist database\factories\UserFactory.php del /q database\factories\UserFactory.php
for %%F in (2014_10_12_000000_create_users_table 2025_12_30_033625_add_role_to_users_table 2026_11_01_000001_create_partners_table 2026_11_01_000002_create_scholarship_schemes_table 2026_11_01_000003_create_applications_table 2026_11_01_000004_create_disbursements_table 2026_11_01_000005_add_confirmation_and_gpa_fields) do if exist "database\migrations\%%F.php" del /q "database\migrations\%%F.php"
if exist resources\views\beasiswa rmdir /s /q resources\views\beasiswa
if exist resources\views\mahasiswa rmdir /s /q resources\views\mahasiswa
for %%F in (confirm-password forgot-password register reset-password verify-email) do if exist "resources\views\auth\%%F.blade.php" del /q "resources\views\auth\%%F.blade.php"
if exist public\build\assets\app-DuZmAW_S.css del /q public\build\assets\app-DuZmAW_S.css
for %%F in (CHECKLIST_KRU DOKUMENTASI_INDEX DOKUMENTASI_KRU IMPLEMENTASI MANIFEST MULAI_DARI_SINI PRESENTASI_BACKEND_FRONTEND PROGRESS_SIMK QUICK_REFERENCE_KRU README_KRU RINGKASAN SELESAI VISUAL_GUIDE) do if exist "%%F.md" del /q "%%F.md"
if exist route_list.txt del /q route_list.txt
echo Selesai. Jalankan: composer dump-autoload
