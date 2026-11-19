<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Tabel t_penyaluran. */
class Penyaluran extends Model
{
    protected $table = 't_penyaluran';
    protected $primaryKey = 'id_penyaluran';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_penyaluran', 'id_penerima', 'termin_ke', 'nominal', 'tgl_penyaluran',
        'no_ref_keuangan', 'status', 'tgl_konfirmasi', 'id_petugas',
    ];

    protected $casts = [
        'nominal' => 'float',
        'tgl_penyaluran' => 'date',
        'tgl_konfirmasi' => 'date',
        'termin_ke' => 'integer',
    ];

    public function penerima()
    {
        return $this->belongsTo(Penerima::class, 'id_penerima', 'id_penerima');
    }

    public function petugas()
    {
        return $this->belongsTo(Pengguna::class, 'id_petugas', 'id_pengguna');
    }
}
