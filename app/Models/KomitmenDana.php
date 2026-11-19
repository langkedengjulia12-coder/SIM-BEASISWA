<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Tabel t_komitmen_dana. */
class KomitmenDana extends Model
{
    protected $table = 't_komitmen_dana';
    protected $primaryKey = 'id_komitmen';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_komitmen', 'id_kontrak', 'id_skema', 'tgl_komitmen', 'nominal_komitmen',
        'nominal_diterima', 'tgl_terima_terakhir', 'keterangan',
    ];

    protected $casts = [
        'tgl_komitmen' => 'date',
        'tgl_terima_terakhir' => 'date',
        'nominal_komitmen' => 'float',
        'nominal_diterima' => 'float',
    ];

    public function kontrak()
    {
        return $this->belongsTo(Kontrak::class, 'id_kontrak', 'id_kontrak');
    }

    public function skema()
    {
        return $this->belongsTo(SkemaBeasiswa::class, 'id_skema', 'id_skema');
    }

    public function getPersenDiterimaAttribute(): float
    {
        return $this->nominal_komitmen > 0
            ? round($this->nominal_diterima / $this->nominal_komitmen * 100, 1)
            : 0.0;
    }
}
