<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Tabel t_mitra. */
class Mitra extends Model
{
    protected $table = 't_mitra';
    protected $primaryKey = 'id_mitra';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public const JENIS = [
        'industri' => 'Industri',
        'lembaga_donor' => 'Lembaga Donor',
        'pemerintah' => 'Pemerintah',
        'perorangan' => 'Perorangan',
    ];

    protected $fillable = [
        'id_mitra', 'id_pengguna', 'nama_mitra', 'jenis_mitra', 'alamat', 'nama_pic',
        'email_pic', 'telepon_pic', 'status_verifikasi', 'tgl_daftar',
    ];

    protected $casts = ['tgl_daftar' => 'date'];

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }

    public function kontrak()
    {
        return $this->hasMany(Kontrak::class, 'id_mitra', 'id_mitra');
    }

    public function getLabelJenisAttribute(): string
    {
        return self::JENIS[$this->jenis_mitra] ?? $this->jenis_mitra;
    }
}
