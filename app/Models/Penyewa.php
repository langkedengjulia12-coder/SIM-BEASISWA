<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Penyewa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'penyewa';

    protected $fillable = [
        'kamar_id',
        'user_id',
        'nama_lengkap',
        'nik',
        'no_hp',
        'email',
        'jenis_kelamin',
        'tanggal_lahir',
        'alamat_asal',
        'pekerjaan',
        'nama_kontak_darurat',
        'no_kontak_darurat',
        'foto_ktp',
        'foto_penyewa',
        'tanggal_masuk',
        'tanggal_keluar',
        'kontrak_mulai',
        'kontrak_selesai',
        'durasi_kontrak_bulan',
        'harga_sewa_kontrak',
        'deposit',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_lahir'       => 'date',
        'tanggal_masuk'       => 'date',
        'tanggal_keluar'      => 'date',
        'kontrak_mulai'       => 'date',
        'kontrak_selesai'     => 'date',
        'harga_sewa_kontrak'  => 'decimal:2',
        'deposit'             => 'decimal:2',
    ];

    // Relations
    public function kamar()
    {
        return $this->belongsTo(Kamar::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class);
    }

    public function utilitas()
    {
        return $this->hasMany(Utilitas::class);
    }

    public function notifikasi()
    {
        return $this->hasMany(Notifikasi::class);
    }

    // Scopes
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    // Accessors
    public function getJenisKelaminLabelAttribute(): string
    {
        return $this->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
    }

    public function getKontrakAktifAttribute(): bool
    {
        return $this->kontrak_selesai >= now()->toDateString() && $this->status === 'aktif';
    }

    public function getSisaHariKontrakAttribute(): int
    {
        return max(0, now()->diffInDays($this->kontrak_selesai, false));
    }

    // Total tunggakan (belum bayar)
    public function getTunggakanAttribute()
    {
        return $this->transaksi()
            ->whereIn('status', ['belum_bayar', 'terlambat'])
            ->sum('jumlah');
    }
}