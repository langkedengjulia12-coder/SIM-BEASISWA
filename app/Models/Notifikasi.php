<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    use HasFactory;

    protected $table = 'notifikasi';

    protected $fillable = [
        'user_id',
        'penyewa_id',
        'judul',
        'pesan',
        'tipe',
        'prioritas',
        'is_read',
        'dibaca_pada',
        'data_tambahan',
    ];

    protected $casts = [
        'is_read'        => 'boolean',
        'dibaca_pada'    => 'datetime',
        'data_tambahan'  => 'array',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function penyewa()
    {
        return $this->belongsTo(Penyewa::class);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Mark as read
    public function markAsRead(): void
    {
        $this->update([
            'is_read'     => true,
            'dibaca_pada' => now(),
        ]);
    }

    // Helper to create notification
    public static function kirim(array $data): self
    {
        if (empty($data['user_id']) && !empty($data['penyewa_id'])) {
            $penyewa = Penyewa::find($data['penyewa_id']);
            if ($penyewa && $penyewa->user_id) {
                $data['user_id'] = $penyewa->user_id;
            }
        }

        if (empty($data['user_id'])) {
            throw new \InvalidArgumentException('Notifikasi membutuhkan user_id yang valid.');
        }

        return static::create($data);
    }

    public static function kirimKeAdmin(array $data): void
    {
        $adminUsers = User::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereIn('role', ['admin', 'pengelola', 'super admin', 'staff'])
                    ->orWhereHas('roles', function ($roleQuery) {
                        $roleQuery->whereIn('name', ['admin', 'pengelola', 'super admin', 'staff']);
                    });
            })
            ->get();

        foreach ($adminUsers as $adminUser) {
            static::create(array_merge($data, [
                'user_id' => $adminUser->id,
            ]));
        }
    }
}