<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/** Tabel t_log_aktivitas (jejak audit). */
class LogAktivitas extends Model
{
    protected $table = 't_log_aktivitas';
    protected $primaryKey = 'id_log';
    public $timestamps = false;

    protected $fillable = ['id_pengguna', 'aksi', 'modul', 'detail', 'ip_address', 'waktu'];

    protected $casts = ['waktu' => 'datetime'];

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }

    /** Catat satu aktivitas pengguna (dipakai seluruh modul). */
    public static function catat(string $aksi, string $modul, ?string $detail = null, ?string $idPengguna = null): self
    {
        return static::create([
            'id_pengguna' => $idPengguna ?? Auth::id(),
            'aksi' => mb_substr($aksi, 0, 30),
            'modul' => mb_substr($modul, 0, 30),
            'detail' => $detail,
            'ip_address' => request()?->ip() ?? '127.0.0.1',
            'waktu' => now(),
        ]);
    }
}
