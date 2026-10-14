# 🎨 VISUAL GUIDE - Menu Structure & Workflow

## 📍 Menu Structure di Admin Dashboard

```
Admin Dashboard
├── Dashboard
├── Manajemen Armada
├── Manajemen Rute
├── Manajemen Driver (tetap ada, dengan icon car-side)
│
├── ★ Manajemen Kru (BARU - dengan dropdown arrow)
│   ├── 📋 Data Kru
│   ├── 💰 Gaji Kru
│   └── 📝 Absensi Kru
│
├── Tracking Pengiriman
├── Manajemen Gudang
├── Jadwal Service
└── Daftar Tarif
```

---

## 📋 Data Kru - List View

```
┌─────────────────────────────────────────────────────────────────┐
│ Data Kru                          [Tambah Kru]                   │
├─────────────────────────────────────────────────────────────────┤
│ No │ Foto │ Nama        │ KTP        │ Telepon    │ Status │ Gaji  │ Aksi    │
├────┼──────┼─────────────┼────────────┼────────────┼────────┼───────┼─────────┤
│ 1  │ [🖼] │ Budi Santoso│ 123456.... │ 081234.... │ Aktif  │ Rp... │ 👁 ✏ 🗑 │
│ 2  │ [🖼] │ Andi Wijaya │ 654321.... │ 089876.... │ Cuti   │ Rp... │ 👁 ✏ 🗑 │
│ 3  │ [🖼] │ Rinto Syah  │ 111222.... │ 082111.... │ Aktif  │ Rp... │ 👁 ✏ 🗑 │
└────┴──────┴─────────────┴────────────┴────────────┴────────┴───────┴─────────┘

Keterangan:
👁 = View/Detail
✏  = Edit
🗑  = Delete
[🖼] = Foto Profil (dengan fallback placeholder)
Status Aktif = Green Badge
Status Cuti = Yellow Badge
Status Tidak Aktif = Red Badge
```

---

## 💰 Gaji Kru - List View

```
┌───────────────────────────────────────────────────────────────────────┐
│ Gaji Kru                              [Tambah Gaji]                   │
├───────────────────────────────────────────────────────────────────────┤
│ No │ Nama Kru   │ Bulan       │ G.Pokok   │ Tunjangan │ Potongan │ G.Bersih  │ Status │ Aksi  │
├────┼────────────┼─────────────┼───────────┼───────────┼──────────┼───────────┼────────┼──────┤
│ 1  │ Budi S.    │ January 2026│ Rp 2.0M  │ Rp 500K  │ Rp 100K  │ Rp 2.4M   │ Paid   │ 👁✏🗑│
│ 2  │ Andi W.    │ January 2026│ Rp 1.8M  │ Rp 400K  │ Rp 50K   │ Rp 2.15M  │ Aprv.  │ 👁✏🗑│
│ 3  │ Rinto S.   │ January 2026│ Rp 2.2M  │ Rp 600K  │ Rp 200K  │ Rp 2.6M   │ Pend.  │ 👁✏🗑│
└────┴────────────┴─────────────┴───────────┴───────────┴──────────┴───────────┴────────┴──────┘

Status Badges:
- Pending = Yellow Badge
- Approved = Blue Badge
- Paid = Green Badge

Potongan = Potongan Alfa + Potongan Lainnya
```

---

## 📝 Absensi Kru - List View

```
┌──────────────────────────────────────────────────────────────────┐
│ Absensi Kru                         [Tambah Absensi]             │
├──────────────────────────────────────────────────────────────────┤
│ No │ Nama Kru   │ Tanggal  │ Tipe    │ Potongan  │ Ket      │ Aksi  │
├────┼────────────┼──────────┼─────────┼───────────┼──────────┼──────┤
│ 1  │ Budi S.    │ 2026-01-05 │ Alfa    │ Rp 150K   │ Tidak ber... │ 👁✏🗑│
│ 2  │ Budi S.    │ 2026-01-15 │ Hadir   │ Rp 0      │ -            │ 👁✏🗑│
│ 3  │ Andi W.    │ 2026-01-10 │ Sakit   │ Rp 100K   │ Demam tinggi │ 👁✏🗑│
│ 4  │ Rinto S.   │ 2026-01-20 │ Cuti    │ Rp 0      │ Izin keluarga│ 👁✏🗑│
└────┴────────────┴──────────┴─────────┴───────────┴──────────┴──────┘

Tipe Badges:
- Hadir = Green Badge
- Alfa = Red Badge
- Sakit = Yellow Badge
- Cuti = Blue Badge
- Lainnya = Gray Badge
```

---

## ✏️ Create Crew - Form View

```
┌─────────────────────────────────────────────────────────────┐
│ Form Tambah Kru                                             │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ [Nama Lengkap *]           [No. KTP *]                     │
│ ┌──────────────────┐      ┌──────────────────┐             │
│ │ Contoh: John Doe │      │ 16 digit         │             │
│ └──────────────────┘      └──────────────────┘             │
│                                                             │
│ [Telepon *]                [Gaji Pokok *]                  │
│ ┌──────────────────┐      ┌──────────────────┐             │
│ │ 081234567890     │      │ Contoh: 2000000  │             │
│ └──────────────────┘      └──────────────────┘             │
│                                                             │
│ [Alamat *]                                                 │
│ ┌──────────────────────────────────────────────┐           │
│ │ Alamat lengkap                               │           │
│ │                                              │           │
│ └──────────────────────────────────────────────┘           │
│                                                             │
│ [Tanggal Bergabung *]      [Status *]                      │
│ ┌──────────────────┐      ┌──────────────────┐             │
│ │ 2025-01-01       │      │ Aktif   ▼        │             │
│ └──────────────────┘      └──────────────────┘             │
│                                                             │
│ [Foto Profil]                                              │
│ ┌─ Pilih file foto ... ─ Browse ─┐                        │
│ Format: JPG, JPEG, PNG. Max: 2MB                           │
│ ┌──────────────────────────────────┐                       │
│ │ [🖼] Preview                     │                       │
│ │ 150x150                          │                       │
│ └──────────────────────────────────┘                       │
│                                                             │
│ [Catatan]                                                  │
│ ┌──────────────────────────────────────────────┐           │
│ │ Catatan tambahan                             │           │
│ │                                              │           │
│ └──────────────────────────────────────────────┘           │
│                                                             │
│ [Simpan]  [Kembali]                                        │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## 💰 Create Salary - Form View

```
┌─────────────────────────────────────────────────────────────┐
│ Form Tambah Gaji Kru                                        │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ [Nama Kru *]                [Bulan *]                      │
│ ┌──────────────────┐      ┌──────────────────┐             │
│ │ -- Pilih Kru --  │      │ YYYY-MM (Month)  │             │
│ │ Budi Santoso  ✓  │      │ 2026-01          │             │
│ │ Andi Wijaya      │      └──────────────────┘             │
│ └──────────────────┘                                        │
│                                                             │
│ ─── KOMPONEN GAJI ───                                      │
│                                                             │
│ [Gaji Pokok *]              [Tunjangan]                    │
│ ┌──────────────────┐      ┌──────────────────┐             │
│ │ 2000000          │      │ 500000           │             │
│ └──────────────────┘      └──────────────────┘             │
│                                                             │
│ [Bonus]                     [Potongan Alfa *]              │
│ ┌──────────────────┐      ┌──────────────────┐             │
│ │ 200000           │      │ 100000           │             │
│ └──────────────────┘      └──────────────────┘             │
│                                                             │
│ [Potongan Lainnya]                                         │
│ ┌──────────────────┐                                       │
│ │ 50000            │                                       │
│ └──────────────────┘                                       │
│                                                             │
│ [Gaji Bersih] (readonly, bold)                            │
│ ┌──────────────────┐                                       │
│ │ Rp 2.550.000     │ ← AUTO CALCULATE                      │
│ └──────────────────┘                                       │
│                                                             │
│ ─── STATUS & PEMBAYARAN ───                               │
│                                                             │
│ [Status *]                  [Tanggal Bayar]                │
│ ┌──────────────────┐      ┌──────────────────┐             │
│ │ Pending   ▼      │      │ 2026-02-05       │             │
│ │ Approved         │      └──────────────────┘             │
│ │ Paid             │                                       │
│ └──────────────────┘                                       │
│                                                             │
│ [Catatan]                                                  │
│ ┌──────────────────────────────────────────────┐           │
│ │ Catatan tambahan                             │           │
│ │                                              │           │
│ └──────────────────────────────────────────────┘           │
│                                                             │
│ [Simpan]  [Kembali]                                        │
│                                                             │
└─────────────────────────────────────────────────────────────┘

FORMULA OTOMATIS:
Gaji Bersih = Gaji Pokok + Tunjangan + Bonus - Potongan Alfa - Potongan Lainnya
            = 2.000.000 + 500.000 + 200.000 - 100.000 - 50.000
            = 2.550.000
```

---

## 📝 Create Absence - Form View

```
┌─────────────────────────────────────────────────────────────┐
│ Form Tambah Absensi Kru                                     │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ [Nama Kru *]                [Tanggal *]                    │
│ ┌──────────────────┐      ┌──────────────────┐             │
│ │ -- Pilih Kru --  │      │ 2026-01-20       │             │
│ │ Budi Santoso  ✓  │      └──────────────────┘             │
│ │ Andi Wijaya      │                                       │
│ └──────────────────┘                                       │
│                                                             │
│ [Tipe Absensi *]            [Potongan Gaji]                │
│ ┌──────────────────┐      ┌──────────────────┐             │
│ │ Hadir      ▼     │      │ 0 (auto for      │             │
│ │ Alfa             │      │ hadir/cuti)      │             │
│ │ Sakit            │      │ 150000 (manual)  │             │
│ │ Cuti             │      └──────────────────┘             │
│ │ Lainnya          │                                       │
│ └──────────────────┘                                       │
│                                                             │
│ [Keterangan]                                               │
│ ┌──────────────────────────────────────────────┐           │
│ │ Keterangan tambahan (optional)               │           │
│ │                                              │           │
│ └──────────────────────────────────────────────┘           │
│                                                             │
│ [Simpan]  [Kembali]                                        │
│                                                             │
└─────────────────────────────────────────────────────────────┘

SMART FIELD:
- Jika Tipe = Hadir/Cuti → Potongan auto 0 (disabled)
- Jika Tipe = Alfa/Sakit/Lainnya → Potongan manual input
```

---

## 👤 Detail Kru - Profile View

```
┌─────────────────────────────────────┬──────────────────────────┐
│ Profil Kru (Left Panel)             │ Riwayat (Right Panel)   │
├─────────────────────────────────────┼──────────────────────────┤
│                                     │                         │
│           [🖼 Foto]                 │ [📋 Gaji] [📝 Absensi]  │
│        Budi Santoso                 │ ├─────────────────────┐ │
│  Active / Cuti / Not Active         │ │ Bulan | Pokok | Pot.│ │
│                                     │ │ Jan26 | Rp2M  | Rp.│ │
│ ├─────────────────────────────────┤ │ │ Des25 | Rp2M  | Rp.│ │
│ │ KTP: 1234567890123456          │ │ └─────────────────────┘ │
│ │ Telepon: 081234567890          │ │                         │
│ │ Alamat: Jl. Merdeka 123        │ │ [📝 Absensi]            │
│ │ Tanggal Gabung: 2025-01-01     │ │ ├─────────────────────┐ │
│ │ Gaji Pokok: Rp 2.000.000       │ │ │ Tgl | Tipe | Pot.  │ │
│ │                                │ │ │5Jan| Alfa | Rp150K│ │
│ ├─────────────────────────────────┤ │ │10Jan| Sakit| Rp100K│ │
│ │ [Edit]                          │ │ │15Jan| Hadir| Rp 0  │ │
│ │                                │ │ └─────────────────────┘ │
│ └─────────────────────────────────┘ │                         │
│                                     │                         │
└─────────────────────────────────────┴──────────────────────────┘

Tabs:
- 📋 Gaji: Tabel riwayat gaji per bulan
- 📝 Absensi: Tabel riwayat absensi
```

---

## 📊 Detail Gaji - View

```
┌──────────────────────────────────────────┬────────────────────┐
│ Detail Gaji Kru                          │ Aksi (Right Panel) │
├──────────────────────────────────────────┼────────────────────┤
│                                          │ [Edit]             │
│ Nama Kru: Budi Santoso                   │ [Hapus]            │
│ Bulan: January 2026                      │ [Kembali]          │
│                                          │                    │
│ ─── KOMPONEN GAJI ───                    │                    │
│                                          │                    │
│ Gaji Pokok: Rp 2.000.000                 │                    │
│ Tunjangan: Rp 500.000                    │                    │
│ Bonus: Rp 200.000                        │                    │
│ Potongan Alfa: -Rp 100.000 (RED)        │                    │
│ Potongan Lainnya: -Rp 50.000 (RED)      │                    │
│                                          │                    │
│ GAJI BERSIH: Rp 2.550.000 (BOLD, BIG)   │                    │
│                                          │                    │
│ ─── STATUS & PEMBAYARAN ───               │                    │
│                                          │                    │
│ Status: [PAID - Green Badge]             │                    │
│ Tanggal Bayar: 2026-02-05                │                    │
│                                          │                    │
│ Catatan: Lorem ipsum...                  │                    │
│                                          │                    │
└──────────────────────────────────────────┴────────────────────┘
```

---

## 🎨 Color & Badge Reference

```
STATUS BADGES:

Crew Status:
├─ Aktif → [Green] badge-success
├─ Cuti → [Yellow] badge-warning
└─ Tidak Aktif → [Red] badge-danger

Salary Status:
├─ Pending → [Yellow] badge-warning
├─ Approved → [Blue] badge-info
└─ Paid → [Green] badge-success

Absence Type:
├─ Hadir → [Green] badge-success
├─ Alfa → [Red] badge-danger
├─ Sakit → [Yellow] badge-warning
├─ Cuti → [Blue] badge-info
└─ Lainnya → [Gray] badge-secondary

NUMERIC DISPLAY:
├─ Positive (Gaji Pokok, Tunjangan, Bonus) → Normal text
├─ Negative (Potongan) → Red text with minus
├─ Gaji Bersih → BOLD, GREEN, BIG font
└─ Currency Format: Rp XXX.XXX.XXX
```

---

## 🔄 User Workflow Example

```
SKENARIO: Manage Gaji Kru Budi Jan 2026

┌─────────────────────────────────────────────────────────────┐
│ STEP 1: Lihat Daftar Gaji                                   │
│ Klik: Manajemen Kru → Gaji Kru                              │
│ Hasil: List semua gaji kru dengan status                    │
└─────────────────────────────────────────────────────────────┘
  ↓
┌─────────────────────────────────────────────────────────────┐
│ STEP 2: Tambah Gaji Baru                                    │
│ Klik: Tambah Gaji                                           │
│ Input:                                                      │
│  - Kru: Budi Santoso                                        │
│  - Bulan: 2026-01                                           │
│  - Gaji Pokok: 2000000                                      │
│  - Tunjangan: 500000                                        │
│  - Bonus: 200000                                            │
│  - Potongan Alfa: 100000 (dari absensi)                    │
│  - Potongan Lainnya: 50000                                  │
│ System: Otomatis hitung Gaji Bersih = 2550000             │
│ Klik: Simpan                                                │
└─────────────────────────────────────────────────────────────┘
  ↓
┌─────────────────────────────────────────────────────────────┐
│ STEP 3: Verifikasi Gaji                                     │
│ Klik: View icon pada baris Budi Jan 2026                    │
│ Lihat: Breakdown lengkap gaji dan status                    │
│ Status sebelumnya: Pending                                  │
└─────────────────────────────────────────────────────────────┘
  ↓
┌─────────────────────────────────────────────────────────────┐
│ STEP 4: Approve Gaji                                        │
│ Klik: Edit icon                                             │
│ Ubah: Status dari Pending → Approved                        │
│       Tanggal Bayar: 2026-02-05                             │
│ Klik: Simpan                                                │
└─────────────────────────────────────────────────────────────┘
  ↓
┌─────────────────────────────────────────────────────────────┐
│ STEP 5: Catat Pembayaran                                    │
│ Klik: Edit icon                                             │
│ Ubah: Status dari Approved → Paid                           │
│ Klik: Simpan                                                │
│ Hasil: Gaji Budi status sudah PAID (Green)                 │
└─────────────────────────────────────────────────────────────┘
```

---

## 📱 Responsive Design Note

Semua view menggunakan Bootstrap 4, jadi:
- ✅ Responsive di desktop (full width)
- ✅ Responsive di tablet (medium layout)
- ✅ Responsive di mobile (stacked layout)

---

**Visual guide selesai! Semua UI sudah jelas digambarkan. 🎨**
