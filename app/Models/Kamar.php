<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kamar extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kamar';

    protected $fillable = [
        'nomor_kamar',
        'nama_kamar',
        'tipe',
        'harga_sewa',
        'luas_m2',
        'lantai',
        'status',
        'fasilitas',
        'deskripsi',
        'foto',
    ];

    protected $casts = [
        'harga_sewa' => 'decimal:2',
        'fasilitas'  => 'array',
    ];

    // Relations
    public function penyewa()
    {
        return $this->hasMany(Penyewa::class);
    }

    public function penyewaAktif()
    {
        return $this->hasOne(Penyewa::class)->where('status', 'aktif');
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class);
    }

    public function utilitas()
    {
        return $this->hasMany(Utilitas::class);
    }

    // Scopes
    public function scopeTersedia($query)
    {
        return $query->where('status', 'tersedia');
    }

    public function scopeTerisi($query)
    {
        return $query->where('status', 'terisi');
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'tersedia'    => 'Tersedia',
            'terisi'      => 'Terisi',
            'maintenance' => 'Maintenance',
            'tidak_aktif' => 'Tidak Aktif',
            default       => '-',
        };
    }

    public function getHargaFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->harga_sewa, 0, ',', '.');
    }
}