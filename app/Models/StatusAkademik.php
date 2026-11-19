<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Tabel t_status_akademik. */
class StatusAkademik extends Model
{
    protected $table = 't_status_akademik';
    protected $primaryKey = 'id_status';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['id_status', 'id_penerima', 'semester', 'ips', 'ipk', 'status', 'tgl_pembaruan'];

    protected $casts = ['ips' => 'float', 'ipk' => 'float', 'tgl_pembaruan' => 'date'];

    public function penerima()
    {
        return $this->belongsTo(Penerima::class, 'id_penerima', 'id_penerima');
    }
}
