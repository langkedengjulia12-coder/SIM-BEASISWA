<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Disbursement extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'amount',
        'disbursement_date',
        'bank_name',
        'account_number',
        'reference_number',
        'status',
    ];

    protected $casts = [
        'application_id' => 'integer',
        'amount' => 'decimal:2',
        'disbursement_date' => 'date',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
