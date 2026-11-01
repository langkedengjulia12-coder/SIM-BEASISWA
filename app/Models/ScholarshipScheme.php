<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScholarshipScheme extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'code',
        'name',
        'category',
        'quota',
        'funding_per_student',
        'academic_year',
        'status',
        'minimum_gpa',
    ];

    protected function casts(): array
    {
        return [
            'partner_id' => 'integer',
            'quota' => 'integer',
            'funding_per_student' => 'decimal:2',
            'minimum_gpa' => 'decimal:2',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
