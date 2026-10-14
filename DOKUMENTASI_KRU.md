# Dokumentasi CRUD Gaji dan Absensi Kru

## Ringkasan Implementasi

Saya telah membuat sistem manajemen lengkap untuk **Kru** yang terpisah dari Driver dengan fitur:
1. **Data Kru** - CRUD untuk informasi kru
2. **Gaji Kru** - Manajemen gaji dengan perhitungan otomatis dan potongan alfa
3. **Absensi Kru** - Pencatatan absensi dengan deduction amount

---

## File yang Dibuat

### 1. Models (`app/Models/`)
- **Crew.php** - Model untuk data kru
- **CrewSalary.php** - Model untuk gaji kru  
- **CrewAbsence.php** - Model untuk absensi kru

### 2. Controllers (`app/Http/Controllers/Admin/`)
- **CrewController.php** - CRUD untuk data kru
- **CrewSalaryController.php** - CRUD untuk gaji kru (otomatis hitung gaji bersih)
- **CrewAbsenceController.php** - CRUD untuk absensi kru

### 3. Migrations (`database/migrations/`)
- **2026_01_20_000000_create_crews_table.php** - Tabel crews
- **2026_01_20_000001_create_crew_salaries_table.php** - Tabel crew_salaries dengan potongan alfa
- **2026_01_20_000002_create_crew_absences_table.php** - Tabel crew_absences

### 4. Views (`resources/views/admin/crew/`)

#### Kru
- `crew/index.blade.php` - Daftar kru dengan foto, gaji pokok, dan status
- `crew/create.blade.php` - Form tambah kru
- `crew/edit.blade.php` - Form edit kru
- `crew/show.blade.php` - Detail kru dengan tab gaji dan absensi

#### Gaji Kru
- `crew/salary/index.blade.php` - Daftar gaji dengan komponen detail
- `crew/salary/create.blade.php` - Form tambah gaji dengan kalkulator otomatis
- `crew/salary/edit.blade.php` - Form edit gaji
- `crew/salary/show.blade.php` - Detail gaji

#### Absensi Kru
- `crew/absence/index.blade.php` - Daftar absensi
- `crew/absence/create.blade.php` - Form tambah absensi
- `crew/absence/edit.blade.php` - Form edit absensi
- `crew/absence/show.blade.php` - Detail absensi

### 5. Routes (`routes/web.php`)
Ditambahkan routes baru:
```php
Route::resource('crew', AdminCrewController::class);
Route::resource('crew.salary', AdminCrewSalaryController::class);
Route::resource('crew.absence', AdminCrewAbsenceController::class);
```

### 6. Menu Navigation (`resources/views/layouts/admin.blade.php`)
Ditambahkan menu "Manajemen Kru" dengan submenu:
- Data Kru
- Gaji Kru  
- Absensi Kru

---

## Fitur Utama

### 1. Data Kru
- Nama lengkap, No. KTP (unik), Telepon, Alamat
- Tanggal bergabung, Status (aktif/tidak aktif/cuti)
- Foto profil dengan preview
- Gaji pokok
- Catatan tambahan

### 2. Gaji Kru
- **Komponen Gaji:**
  - Gaji Pokok
  - Tunjangan
  - Bonus
  - Potongan Alfa (untuk absensi)
  - Potongan Lainnya
  
- **Otomatis menghitung:** Gaji Bersih = Gaji Pokok + Tunjangan + Bonus - Potongan Alfa - Potongan Lainnya

- **Status:** Pending, Approved, Paid
- **Tanggal Pembayaran:** Opsional
- **Validasi:** Unik per kru per bulan (tidak bisa duplikat)

### 3. Absensi Kru
- **Tipe Absensi:** Hadir, Alfa, Sakit, Cuti, Lainnya
- **Potongan Gaji:** Otomatis 0 untuk Hadir/Cuti
- **Keterangan:** Catatan tambahan
- **Validasi:** Unik per kru per tanggal

---

## Cara Menggunakan

### 1. Jalankan Migration
```bash
cd c:\laragon\www\armada-project
php artisan migrate
```

### 2. Akses Menu Admin
- Setelah login sebagai Super Admin atau Staff
- Navigasi ke **Manajemen Kru** di sidebar

### 3. Workflow Penggunaan

#### Tambah Data Kru
1. Klik "Manajemen Kru" → "Data Kru"
2. Klik "Tambah Kru"
3. Isi form dengan data kru
4. Upload foto
5. Simpan

#### Tambah Gaji Kru
1. Klik "Manajemen Kru" → "Gaji Kru"
2. Klik "Tambah Gaji"
3. Pilih kru
4. Pilih bulan (format YYYY-MM)
5. Isi komponen gaji
6. Gaji bersih otomatis dihitung
7. Tentukan status dan tanggal pembayaran
8. Simpan

#### Catat Absensi Kru
1. Klik "Manajemen Kru" → "Absensi Kru"
2. Klik "Tambah Absensi"
3. Pilih kru dan tanggal
4. Pilih tipe absensi
5. Masukkan potongan gaji (jika ada)
6. Tambahkan keterangan
7. Simpan

---

## Database Schema

### Tabel: crews
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id_crew | BIGINT (PK) | Primary key |
| nama | VARCHAR | Nama lengkap kru |
| no_ktp | VARCHAR (UNIQUE) | No KTP, 16 digit |
| telepon | VARCHAR | Nomor telepon |
| alamat | TEXT | Alamat lengkap |
| status_aktif | ENUM | aktif, tidak_aktif, cuti |
| tanggal_gabung | DATE | Tanggal bergabung |
| foto | VARCHAR | Path foto profil |
| gaji_pokok | DECIMAL(10,2) | Gaji pokok per bulan |
| catatan | TEXT | Catatan tambahan |
| timestamps | - | created_at, updated_at |

### Tabel: crew_salaries
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id_crew_salary | BIGINT (PK) | Primary key |
| id_crew | BIGINT (FK) | Foreign key ke crews |
| bulan | DATE (INDEX) | Bulan gaji (tanggal selalu 1) |
| gaji_pokok | DECIMAL(10,2) | Gaji pokok |
| tunjangan | DECIMAL(10,2) | Tunjangan |
| bonus | DECIMAL(10,2) | Bonus |
| potongan_alfa | DECIMAL(10,2) | Potongan untuk alfa |
| potongan_lainnya | DECIMAL(10,2) | Potongan lain |
| gaji_bersih | DECIMAL(10,2) | Hasil kalkulasi |
| status | ENUM | pending, approved, paid |
| tanggal_bayar | DATE | Tanggal pembayaran |
| catatan | TEXT | Catatan |
| timestamps | - | created_at, updated_at |
| **UNIQUE** | (id_crew, bulan) | Tidak boleh duplikat per bulan |

### Tabel: crew_absences
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id_crew_absence | BIGINT (PK) | Primary key |
| id_crew | BIGINT (FK) | Foreign key ke crews |
| tanggal | DATE | Tanggal absensi |
| tipe | ENUM | hadir, alfa, sakit, cuti, lainnya |
| deduction_amount | DECIMAL(10,2) | Jumlah potongan gaji |
| keterangan | TEXT | Keterangan |
| timestamps | - | created_at, updated_at |
| **UNIQUE** | (id_crew, tanggal) | Tidak boleh duplikat per hari |

---

## Relasi Model

### Crew
```php
- hasMany CrewSalary (one to many)
- hasMany CrewAbsence (one to many)
```

### CrewSalary
```php
- belongsTo Crew
```

### CrewAbsence
```php
- belongsTo Crew
```

---

## Fitur Keamanan

1. **Authentication:** Hanya Super Admin dan Staff yang bisa akses
2. **Authorization:** Sudah menggunakan middleware role
3. **Validasi:** 
   - KTP unik per kru
   - Gaji unik per kru per bulan
   - Absensi unik per kru per tanggal
4. **Notification:** Setiap aksi (create/update/delete) mengirim notifikasi
5. **Soft Delete:** Belum diimplementasi (bisa ditambahkan jika diperlukan)

---

## Customization

### Menambah Field Gaji
Edit di `CrewSalaryController` dan migration untuk menambah kolom baru.

### Mengubah Tipe Absensi
Edit enum di `crew_absences` migration:
```php
$table->enum('tipe', ['hadir', 'alfa', 'sakit', 'cuti', 'lainnya']);
```

### Mengubah Format Gaji
Ubah di view `crew/salary/create.blade.php` dan `edit.blade.php` fungsi `calculateGajiBersih()`.

---

## Catatan Penting

1. **Migration:** Pastikan menjalankan `php artisan migrate` sebelum menggunakan
2. **Storage:** Foto kru disimpan di `storage/app/public/crew/`
3. **Route Names:** Gunakan `route('admin.crew.index')`, `route('admin.crew.salary.index')`, dll
4. **Layout:** Sudah terintegrasi dengan AdminLTE template yang ada
5. **Styling:** Menggunakan Bootstrap 4 yang sudah ada di project

---

## Testing

Untuk testing, pastikan:
1. Sudah login sebagai Super Admin atau Staff
2. Navigasi ke menu "Manajemen Kru"
3. Test CRUD untuk Data Kru, Gaji, dan Absensi
4. Verifikasi kalkulasi gaji otomatis
5. Cek notifikasi terkirim untuk setiap aksi

---

**Selesai!** Sistem manajemen Kru sudah siap digunakan.
