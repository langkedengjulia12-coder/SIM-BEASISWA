<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Tabel t_skema_beasiswa. */
class SkemaBeasiswa extends Model
{
    protected $table = 't_skema_beasiswa';
    protected $primaryKey = 'id_skema';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public const JENIS = [
        'prestasi' => 'Beasiswa Prestasi',
        'bantuan_biaya' => 'Bantuan Biaya Pendidikan',
        'riset' => 'Beasiswa Berbasis Riset',
    ];

    public const ATURAN = [
        'sekaligus' => 'Sekaligus',
        'per_semester' => 'Per Semester',
        'bulanan' => 'Bulanan',
    ];

    protected $fillable = [
        'id_skema', 'nama_skema', 'jenis', 'kuota', 'nominal_per_penerima', 'ipk_minimum',
        'kriteria_seleksi', 'tgl_buka', 'tgl_tutup', 'aturan_penyaluran', 'status',
    ];

    protected $casts = [
        'kuota' => 'integer',
        'nominal_per_penerima' => 'float',
        'ipk_minimum' => 'float',
        'tgl_buka' => 'date',
        'tgl_tutup' => 'date',
    ];

    public function komitmen()
    {
        return $this->hasMany(KomitmenDana::class, 'id_skema', 'id_skema');
    }

    public function pendaftaran()
    {
        return $this->hasMany(Pendaftaran::class, 'id_skema', 'id_skema');
    }

    public function penerima()
    {
        return $this->hasMany(Penerima::class, 'id_skema', 'id_skema');
    }

    /** Bobot kriteria sebagai array: akademik, nonakademik, kebutuhan (persen). */
    public function getBobotAttribute(): array
    {
        $b = json_decode((string) $this->kriteria_seleksi, true) ?: [];

        return [
            'akademik' => (float) ($b['akademik'] ?? 0),
            'nonakademik' => (float) ($b['nonakademik'] ?? 0),
            'kebutuhan' => (float) ($b['kebutuhan'] ?? 0),
        ];
    }

    public function getJumlahTerminAttribute(): int
    {
        return (int) (config('beasiswa.termin')[$this->aturan_penyaluran] ?? 1);
    }

    /** ALGO-05: kebutuhan dana = kuota x nominal_per_penerima x jumlah termin. */
    public function getKebutuhanDanaAttribute(): float
    {
        return $this->kuota * $this->nominal_per_penerima * $this->jumlah_termin;
    }

    public function getDokumenWajibAttribute(): array
    {
        return config('beasiswa.dokumen_wajib')[$this->jenis] ?? [];
    }

    public function getLabelJenisAttribute(): string
    {
        return self::JENIS[$this->jenis] ?? $this->jenis;
    }

    /** Total komitmen dari kontrak yang masih berlaku dan terkait skema ini. */
    public function totalKomitmen(): float
    {
        return (float) $this->komitmen()
            ->whereHas('kontrak', fn ($q) => $q->berlaku())
            ->sum('nominal_komitmen');
    }

    public function totalTersalur(): float
    {
        return (float) Penyaluran::whereIn('status', ['tersalurkan', 'dikonfirmasi'])
            ->whereHas('penerima', fn ($q) => $q->where('id_skema', $this->id_skema))
            ->sum('nominal');
    }

    /** Sisa komitmen terkait = total komitmen - dana yang sudah tersalur. */
    public function sisaKomitmen(): float
    {
        return $this->totalKomitmen() - $this->totalTersalur();
    }

    public function sudahDitetapkan(): bool
    {
        return $this->penerima()->exists();
    }

    public function dalamPeriode(): bool
    {
        $h = now()->startOfDay();

        return $h->betweenIncluded($this->tgl_buka->startOfDay(), $this->tgl_tutup->startOfDay());
    }
}
