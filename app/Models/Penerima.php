<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Tabel t_penerima. */
class Penerima extends Model
{
    protected $table = 't_penerima';
    protected $primaryKey = 'id_penerima';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_penerima', 'id_pendaftaran', 'id_skema', 'npm', 'no_sk', 'tgl_penetapan', 'status_penerima',
    ];

    protected $casts = ['tgl_penetapan' => 'date'];

    public function pendaftaran()
    {
        return $this->belongsTo(Pendaftaran::class, 'id_pendaftaran', 'id_pendaftaran');
    }

    public function skema()
    {
        return $this->belongsTo(SkemaBeasiswa::class, 'id_skema', 'id_skema');
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'npm', 'npm');
    }

    public function penyaluran()
    {
        return $this->hasMany(Penyaluran::class, 'id_penerima', 'id_penerima');
    }

    public function statusAkademik()
    {
        return $this->hasMany(StatusAkademik::class, 'id_penerima', 'id_penerima');
    }

    public function statusAkademikTerakhir()
    {
        return $this->hasOne(StatusAkademik::class, 'id_penerima', 'id_penerima')->latestOfMany('semester');
    }
}
