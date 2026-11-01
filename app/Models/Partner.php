<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'mou_number',
        'mou_start',
        'mou_end',
        'funding_amount',
        'status',
    ];

    protected $casts = [
        'mou_start' => 'date',
        'mou_end' => 'date',
        'funding_amount' => 'decimal:2',
    ];

    public function scholarshipSchemes(): HasMany
    {
        return $this->hasMany(ScholarshipScheme::class);
    }
}
