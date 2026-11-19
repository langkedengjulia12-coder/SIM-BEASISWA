<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Tabel t_kontrak (MoU / MoA). */
class Kontrak extends Model
{
    protected $table = 't_kontrak';
    protected $primaryKey = 'id_kontrak';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_kontrak', 'id_mitra', 'no_dokumen', 'judul', 'jenis_dokumen', 'tgl_mulai',
        'tgl_berakhir', 'nilai_komitmen', 'status', 'file_dokumen',
    ];

    protected $casts = [
        'tgl_mulai' => 'date',
        'tgl_berakhir' => 'date',
        'nilai_komitmen' => 'float',
    ];

    public function mitra()
    {
        return $this->belongsTo(Mitra::class, 'id_mitra', 'id_mitra');
    }

    public function komitmen()
    {
        return $this->hasMany(KomitmenDana::class, 'id_kontrak', 'id_kontrak');
    }

    public function scopeBerlaku($q)
    {
        return $q->whereIn('status', ['aktif', 'diperpanjang']);
    }

    /** Sisa hari sampai berakhir (negatif bila sudah lewat). */
    public function getSisaHariAttribute(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->tgl_berakhir->startOfDay(), false);
    }
}
