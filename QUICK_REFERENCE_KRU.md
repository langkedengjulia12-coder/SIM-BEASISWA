# QUICK REFERENCE - CRUD KRU, GAJI & ABSENSI

## 🚀 Quick Start

```bash
# 1. Jalankan migration
php artisan migrate

# 2. Akses admin panel
# Login dengan Super Admin atau Staff

# 3. Navigasi ke Manajemen Kru di sidebar
```

---

## 📋 Menu Navigation Path

```
Admin Dashboard
└── Manajemen Kru
    ├── Data Kru
    │   ├── Tambah Kru
    │   ├── View/Edit/Delete Kru
    │   └── Lihat Riwayat Gaji & Absensi di Detail
    │
    ├── Gaji Kru
    │   ├── Tambah Gaji
    │   ├── View/Edit/Delete Gaji
    │   └── Lihat Breakdown Komponen Gaji
    │
    └── Absensi Kru
        ├── Tambah Absensi
        ├── View/Edit/Delete Absensi
        └── Lihat Potongan Gaji per Absensi
```

---

## 📂 File Structure Quick View

### Models
```
app/Models/
├── Crew.php
├── CrewSalary.php
└── CrewAbsence.php
```

### Controllers
```
app/Http/Controllers/Admin/
├── CrewController.php
├── CrewSalaryController.php
└── CrewAbsenceController.php
```

### Views
```
resources/views/admin/crew/
├── index.blade.php
├── create.blade.php
├── edit.blade.php
├── show.blade.php
├── salary/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
└── absence/
    ├── index.blade.php
    ├── create.blade.php
    ├── edit.blade.php
    └── show.blade.php
```

### Migrations
```
database/migrations/
├── 2026_01_20_000000_create_crews_table.php
├── 2026_01_20_000001_create_crew_salaries_table.php
└── 2026_01_20_000002_create_crew_absences_table.php
```

---

## 🔄 Field Reference

### Crew Form Fields
```
✓ Nama Lengkap (required)
✓ No. KTP (required, 16 digit, unique)
✓ Telepon (required)
✓ Alamat (required)
✓ Tanggal Bergabung (required)
✓ Status (required: aktif/tidak_aktif/cuti)
✓ Gaji Pokok (required)
✓ Foto Profil (optional, max 2MB)
✓ Catatan (optional)
```

### Salary Form Fields
```
✓ Nama Kru (required)
✓ Bulan (required, format YYYY-MM)
✓ Gaji Pokok (required)
✓ Tunjangan (optional, default 0)
✓ Bonus (optional, default 0)
✓ Potongan Alfa (optional, default 0)
✓ Potongan Lainnya (optional, default 0)
  → Gaji Bersih (AUTO CALCULATE)
✓ Status (required: pending/approved/paid)
✓ Tanggal Bayar (optional)
✓ Catatan (optional)
```

### Absence Form Fields
```
✓ Nama Kru (required)
✓ Tanggal (required)
✓ Tipe (required: hadir/alfa/sakit/cuti/lainnya)
✓ Potongan Gaji (optional)
  → Auto 0 untuk: hadir, cuti, lainnya
  → Manual input untuk: alfa, sakit
✓ Keterangan (optional)
```

---

## 💰 Rumus Perhitungan Gaji

```
Gaji Bersih = Gaji Pokok + Tunjangan + Bonus - Potongan Alfa - Potongan Lainnya

Contoh:
- Gaji Pokok: 2.000.000
- Tunjangan: 500.000
- Bonus: 250.000
- Potongan Alfa: 150.000
- Potongan Lainnya: 50.000

Gaji Bersih = 2.000.000 + 500.000 + 250.000 - 150.000 - 50.000
            = 2.550.000
```

---

## ✅ Validasi & Constraints

### Database Constraints
```
Crews:
  - no_ktp UNIQUE

Crew_Salaries:
  - (id_crew, bulan) UNIQUE
  - id_crew foreign key to crews

Crew_Absences:
  - (id_crew, tanggal) UNIQUE
  - id_crew foreign key to crews
```

### Form Validations
```
Crew:
  - nama: required, string, max:255
  - no_ktp: required, unique, size:16
  - telepon: required, string
  - alamat: required, string
  - status_aktif: required, in:aktif,tidak_aktif,cuti
  - tanggal_gabung: required, date
  - foto: nullable, image, mimes:jpeg,png,jpg, max:2048
  - gaji_pokok: required, numeric, min:0

CrewSalary:
  - id_crew: required, exists:crews
  - bulan: required, date, format:Y-m-01
  - gaji_pokok: required, numeric, min:0
  - tunjangan/bonus/potongan: nullable, numeric, min:0
  - status: required, in:pending,approved,paid

CrewAbsence:
  - id_crew: required, exists:crews
  - tanggal: required, date
  - tipe: required, in:hadir,alfa,sakit,cuti,lainnya
  - deduction_amount: nullable, numeric, min:0
```

---

## 🎨 Status Badges Colors

```
Crew Status:
  ✓ Aktif → Green (badge-success)
  ✓ Cuti → Yellow (badge-warning)
  ✓ Tidak Aktif → Red (badge-danger)

Salary Status:
  ✓ Pending → Yellow (badge-warning)
  ✓ Approved → Blue (badge-info)
  ✓ Paid → Green (badge-success)

Absence Type:
  ✓ Hadir → Green (badge-success)
  ✓ Alfa → Red (badge-danger)
  ✓ Sakit → Yellow (badge-warning)
  ✓ Cuti → Blue (badge-info)
  ✓ Lainnya → Gray (badge-secondary)
```

---

## 🔑 Important Routes

```php
// Crew Routes
route('admin.crew.index')      // GET /admin/crew
route('admin.crew.create')     // GET /admin/crew/create
route('admin.crew.store')      // POST /admin/crew
route('admin.crew.show', $crew) // GET /admin/crew/{crew}
route('admin.crew.edit', $crew) // GET /admin/crew/{crew}/edit
route('admin.crew.update', $crew) // PUT /admin/crew/{crew}
route('admin.crew.destroy', $crew) // DELETE /admin/crew/{crew}

// Salary Routes
route('admin.crew.salary.index')      // GET /admin/crew/salary
route('admin.crew.salary.create')     // GET /admin/crew/salary/create
route('admin.crew.salary.store')      // POST /admin/crew/salary
route('admin.crew.salary.show', $salary)    // GET /admin/crew/salary/{salary}
route('admin.crew.salary.edit', $salary)    // GET /admin/crew/salary/{salary}/edit
route('admin.crew.salary.update', $salary)  // PUT /admin/crew/salary/{salary}
route('admin.crew.salary.destroy', $salary) // DELETE /admin/crew/salary/{salary}

// Absence Routes
route('admin.crew.absence.index')      // GET /admin/crew/absence
route('admin.crew.absence.create')     // GET /admin/crew/absence/create
route('admin.crew.absence.store')      // POST /admin/crew/absence
route('admin.crew.absence.show', $absence)    // GET /admin/crew/absence/{absence}
route('admin.crew.absence.edit', $absence)    // GET /admin/crew/absence/{absence}/edit
route('admin.crew.absence.update', $absence)  // PUT /admin/crew/absence/{absence}
route('admin.crew.absence.destroy', $absence) // DELETE /admin/crew/absence/{absence}
```

---

## 🛠️ Troubleshooting

### Migration Error
```
Error: SQLSTATE[HY000]: General error...

Solution:
1. Pastikan database config benar di .env
2. Jalankan php artisan fresh untuk reset
3. Atau delete existing tables dan jalankan migrate
```

### Foto tidak tampil
```
Solution:
1. Pastikan folder storage/app/public/crew/ ada
2. Jalankan: php artisan storage:link
3. Pastikan symbolic link ke public/storage sudah ada
```

### Gaji tidak terhitung
```
Solution:
1. Refresh page (refresh JavaScript)
2. Cek console untuk error
3. Pastikan semua field input filled dengan number
4. Cek form submit (POST/PUT) berhasil
```

### Unique constraint error
```
Error saat simpan gaji/absensi yang duplikat

Solution:
1. Lihat message error apa
2. Untuk gaji: jangan duplikat per kru per bulan
3. Untuk absensi: jangan duplikat per kru per tanggal
4. Edit record existing jika ingin update
```

---

## 📊 Example Data Entry

### Contoh Tambah Kru
```
Nama: Budi Santoso
KTP: 1234567890123456
Telepon: 081234567890
Alamat: Jl. Merdeka No. 123, Jakarta
Tanggal Gabung: 2025-01-01
Status: Aktif
Gaji Pokok: 2000000
Catatan: Kru berpengalaman
```

### Contoh Tambah Gaji
```
Kru: Budi Santoso
Bulan: 2026-01
Gaji Pokok: 2000000
Tunjangan: 500000
Bonus: 200000
Potongan Alfa: 100000
Potongan Lainnya: 0
→ Gaji Bersih: 2600000
Status: Approved
Tanggal Bayar: 2026-02-05
```

### Contoh Tambah Absensi
```
Kru: Budi Santoso
Tanggal: 2026-01-20
Tipe: Alfa
Potongan Gaji: 150000
Keterangan: Tidak memberitahu
```

---

## 🔐 Security Notes

✓ Hanya Super Admin & Staff yang bisa akses
✓ Semua input di-validate
✓ File upload hanya image
✓ Unique constraints di database
✓ Foreign key relationships dijaga
✓ Soft delete bisa diaktifkan
✓ Notification untuk audit trail

---

## 📚 Related Documentation

- **DOKUMENTASI_KRU.md** - Full documentation
- **CHECKLIST_KRU.md** - Implementation checklist
- **README_KRU.md** - Summary & overview

---

**Status: ✅ READY TO USE**

Semua siap! Tinggal migrate dan gunakan.
