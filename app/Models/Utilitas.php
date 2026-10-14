<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Utilitas extends Model
{
    use HasFactory;

    protected $table = 'utilitas';

    protected $fillable = [
        'kamar_id',
        'penyewa_id',
        'tarif_id',
        'jenis',
        'periode_bulan',
        'periode_tahun',
        'meter_awal',
        'meter_akhir',
        'meteran_awal',
        'meteran_akhir',
        'penggunaan',
        'harga_satuan',
        'total_biaya',
        'harga_per_unit',
        'tanggal_catat',
        'status_bayar',
        'transaksi_id',
        'catatan',
    ];

    protected $casts = [
        'meteran_awal'    => 'decimal:2',
        'meteran_akhir'   => 'decimal:2',
        'pemakaian'       => 'decimal:2',
        'harga_per_unit'  => 'decimal:2',
        'total_tagihan'   => 'decimal:2',
        'tanggal_catat'   => 'date',
    ];

    // Relations
    public function kamar()
    {
        return $this->belongsTo(Kamar::class);
    }

    public function penyewa()
    {
        return $this->belongsTo(Penyewa::class);
    }

    public function tarif()
    {
        return $this->belongsTo(TarifUtilitas::class, 'tarif_id');
    }

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    // Accessors
    public function getJenisLabelAttribute(): string
    {
        return $this->jenis === 'listrik' ? 'Listrik (kWh)' : 'Air (m³)';
    }

    public function getTotalFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->total_tagihan, 0, ',', '.');
    }
}