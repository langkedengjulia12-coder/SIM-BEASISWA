<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TarifUtilitas extends Model
{
    use HasFactory;

    protected $table = 'tarif_utilitas';

    protected $fillable = [
        'jenis',
        'harga_per_unit',
        'satuan',
        'berlaku_mulai',
        'berlaku_sampai',
        'is_active',
        'keterangan',
    ];

    protected $casts = [
        'harga_per_unit' => 'decimal:2',
        'berlaku_mulai' => 'date',
        'berlaku_sampai' => 'date',
        'is_active' => 'boolean',
    ];

    public static function getAktif(string $jenis): ?self
    {
        return self::where('jenis', $jenis)
            ->where('is_active', true)
            ->orderByDesc('berlaku_mulai')
            ->first();
    }
}
