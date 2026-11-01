<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'scholarship_scheme_id',
        'registration_number',
        'study_program',
        'gpa',
        'status',
        'submitted_at',
        'mutation_file',
        'confirmation_status',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'scholarship_scheme_id' => 'integer',
            'gpa' => 'decimal:2',
            'submitted_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scholarshipScheme(): BelongsTo
    {
        return $this->belongsTo(ScholarshipScheme::class);
    }
}
