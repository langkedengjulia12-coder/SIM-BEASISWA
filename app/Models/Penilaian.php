<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Tabel t_penilaian. */
class Penilaian extends Model
{
    protected $table = 't_penilaian';
    protected $primaryKey = 'id_penilaian';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_penilaian', 'id_pendaftaran', 'nilai_akademik', 'nilai_nonakademik',
        'nilai_kebutuhan', 'nilai_total', 'peringkat', 'id_penilai', 'tgl_penilaian',
    ];

    protected $casts = [
        'nilai_akademik' => 'float',
        'nilai_nonakademik' => 'float',
        'nilai_kebutuhan' => 'float',
        'nilai_total' => 'float',
        'tgl_penilaian' => 'datetime',
    ];

    public function pendaftaran()
    {
        return $this->belongsTo(Pendaftaran::class, 'id_pendaftaran', 'id_pendaftaran');
    }

    public function penilai()
    {
        return $this->belongsTo(Pengguna::class, 'id_penilai', 'id_pengguna');
    }
}
