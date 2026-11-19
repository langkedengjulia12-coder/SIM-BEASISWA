<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Tabel t_dokumen_pendaftaran. */
class DokumenPendaftaran extends Model
{
    protected $table = 't_dokumen_pendaftaran';
    protected $primaryKey = 'id_dokumen';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_dokumen', 'id_pendaftaran', 'jenis_dokumen', 'nama_file', 'path_file',
        'ukuran_kb', 'status_verifikasi', 'tgl_unggah',
    ];

    protected $casts = ['tgl_unggah' => 'datetime', 'ukuran_kb' => 'integer'];

    public function pendaftaran()
    {
        return $this->belongsTo(Pendaftaran::class, 'id_pendaftaran', 'id_pendaftaran');
    }

    public function getLabelAttribute(): string
    {
        return config('beasiswa.dokumen_label')[$this->jenis_dokumen] ?? $this->jenis_dokumen;
    }
}
