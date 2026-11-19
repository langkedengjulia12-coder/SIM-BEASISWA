<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Tabel t_mahasiswa. */
class Mahasiswa extends Model
{
    protected $table = 't_mahasiswa';
    protected $primaryKey = 'npm';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'npm', 'id_pengguna', 'nama_lengkap', 'program_studi', 'fakultas',
        'angkatan', 'ipk', 'status_akademik', 'no_rekening', 'nama_bank',
    ];

    protected $casts = [
        'ipk' => 'float',
        'angkatan' => 'integer',
        'no_rekening' => 'encrypted',   // AES-256 (cipher aplikasi) - SKPL 2.1
    ];

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }

    public function pendaftaran()
    {
        return $this->hasMany(Pendaftaran::class, 'npm', 'npm');
    }

    public function penerima()
    {
        return $this->hasMany(Penerima::class, 'npm', 'npm');
    }

    public function getRekeningTersamarAttribute(): string
    {
        $no = (string) $this->no_rekening;

        return $no === '' ? '-' : str_repeat('*', max(strlen($no) - 4, 0)).substr($no, -4);
    }
}
