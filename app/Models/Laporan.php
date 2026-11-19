<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Tabel t_laporan (arsip laporan & ekspor). */
class Laporan extends Model
{
    protected $table = 't_laporan';
    protected $primaryKey = 'id_laporan';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_laporan', 'jenis_laporan', 'id_mitra', 'periode_awal', 'periode_akhir',
        'format_file', 'path_file', 'id_pembuat', 'tgl_generate',
    ];

    protected $casts = [
        'periode_awal' => 'date',
        'periode_akhir' => 'date',
        'tgl_generate' => 'datetime',
    ];

    public function mitra()
    {
        return $this->belongsTo(Mitra::class, 'id_mitra', 'id_mitra');
    }

    public function pembuat()
    {
        return $this->belongsTo(Pengguna::class, 'id_pembuat', 'id_pengguna');
    }
}
