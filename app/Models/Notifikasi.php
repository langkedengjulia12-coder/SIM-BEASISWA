<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Tabel t_notifikasi. */
class Notifikasi extends Model
{
    protected $table = 't_notifikasi';
    protected $primaryKey = 'id_notifikasi';
    public $timestamps = false;

    protected $fillable = ['id_pengguna', 'jenis', 'judul', 'isi', 'dibaca', 'tgl_kirim'];

    protected $casts = ['dibaca' => 'boolean', 'tgl_kirim' => 'datetime'];

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }
}
