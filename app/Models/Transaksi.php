<?php

namespace App\Models;

use App\Models\Utilitas;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaksi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'transaksi';

    protected $fillable = [
        'kode_transaksi',
        'penyewa_id',
        'kamar_id',
        'jenis',
        'keterangan',
        'jumlah',
        'tanggal_tagihan',
        'jatuh_tempo',
        'tanggal_bayar',
        'metode_bayar',
        'status',
        'bukti_bayar',
        'periode_bulan',
        'periode_tahun',
        'catatan',
        'dibuat_oleh',
    ];

    protected $casts = [
        'jumlah'           => 'decimal:2',
        'tanggal_tagihan'  => 'date',
        'jatuh_tempo'      => 'date',
        'tanggal_bayar'    => 'date',
    ];

    // Auto generate kode transaksi
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaksi) {
            if (empty($transaksi->kode_transaksi)) {
                $transaksi->kode_transaksi = 'TRX-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
            }
            // Auto update status jika melewati jatuh tempo
            if ($transaksi->status === 'belum_bayar' && $transaksi->jatuh_tempo < now()->toDateString()) {
                $transaksi->status = 'terlambat';
            }
        });

        static::updated(function ($transaksi) {
            if ($transaksi->isDirty('status') && $transaksi->status === 'lunas' && $transaksi->jenis === 'utilitas') {
                Utilitas::where('transaksi_id', $transaksi->id)
                    ->update(['status_bayar' => 'sudah']);

                Utilitas::where('penyewa_id', $transaksi->penyewa_id)
                    ->where('kamar_id', $transaksi->kamar_id)
                    ->where('periode_bulan', $transaksi->periode_bulan)
                    ->where('periode_tahun', $transaksi->periode_tahun)
                    ->where('status_bayar', 'belum')
                    ->update(['status_bayar' => 'sudah', 'transaksi_id' => $transaksi->id]);
            }
        });
    }

    // Relations
    public function penyewa()
    {
        return $this->belongsTo(Penyewa::class);
    }

    public function kamar()
    {
        return $this->belongsTo(Kamar::class);
    }

    public function dibuatOleh()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function utilitas()
    {
        return $this->hasMany(Utilitas::class, 'transaksi_id');
    }

    // Scopes
    public function scopeBelumBayar($query)
    {
        return $query->whereIn('status', ['belum_bayar', 'terlambat']);
    }

    public function scopeLunas($query)
    {
        return $query->where('status', 'lunas');
    }

    public function scopePeriode($query, int $bulan, int $tahun)
    {
        return $query->where('periode_bulan', $bulan)->where('periode_tahun', $tahun);
    }

    public function scopeTagihanBulanIni($query, int $penyewaId)
    {
        return $query
            ->where('penyewa_id', $penyewaId)
            ->whereIn('status', ['belum_bayar', 'terlambat'])
            ->whereDate('jatuh_tempo', '>=', now()->toDateString())
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->whereNotNull('periode_bulan')
                        ->whereNotNull('periode_tahun')
                        ->where('periode_bulan', now()->month)
                        ->where('periode_tahun', now()->year);
                })->orWhere(function ($query) {
                    $query->whereMonth('jatuh_tempo', now()->month)
                        ->whereYear('jatuh_tempo', now()->year);
                });
            });
    }

    public function scopeTunggakan($query, int $penyewaId)
    {
        return $query
            ->where('penyewa_id', $penyewaId)
            ->whereIn('status', ['belum_bayar', 'terlambat'])
            ->whereDate('jatuh_tempo', '<', now()->toDateString());
    }

    // Accessors
    public function getJumlahFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->jumlah, 0, ',', '.');
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'belum_bayar' && $this->jatuh_tempo < now()->toDateString();
    }

    public function getLabelAttribute(): string
    {
        $keterangan = trim((string) ($this->keterangan ?? ''));

        if ($keterangan !== '') {
            return ucfirst($keterangan);
        }

        $periode = $this->periode_bulan && $this->periode_tahun
            ? " periode {$this->periode_bulan}/{$this->periode_tahun}"
            : '';

        return match ($this->jenis) {
            'utilitas' => 'Tagihan utilitas' . $periode,
            'sewa' => 'Tagihan sewa' . $periode,
            default => ucfirst($this->jenis),
        };
    }
}