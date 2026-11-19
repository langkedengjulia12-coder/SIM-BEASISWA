<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Tabel t_pendaftaran. */
class Pendaftaran extends Model
{
    protected $table = 't_pendaftaran';
    protected $primaryKey = 'id_pendaftaran';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public const STATUS = [
        'diajukan', 'tidak_lengkap', 'lolos_administrasi', 'dinilai', 'diterima', 'cadangan', 'ditolak',
    ];

    protected $fillable = [
        'id_pendaftaran', 'npm', 'id_skema', 'tgl_daftar', 'ipk_saat_daftar', 'motivasi',
        'status', 'catatan_verifikasi',
    ];

    protected $casts = [
        'tgl_daftar' => 'datetime',
        'ipk_saat_daftar' => 'float',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'npm', 'npm');
    }

    public function skema()
    {
        return $this->belongsTo(SkemaBeasiswa::class, 'id_skema', 'id_skema');
    }

    public function dokumen()
    {
        return $this->hasMany(DokumenPendaftaran::class, 'id_pendaftaran', 'id_pendaftaran');
    }

    public function penilaian()
    {
        return $this->hasOne(Penilaian::class, 'id_pendaftaran', 'id_pendaftaran');
    }

    public function penerima()
    {
        return $this->hasOne(Penerima::class, 'id_pendaftaran', 'id_pendaftaran');
    }
}
