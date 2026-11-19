<?php

namespace App\Models;

use App\Support\Kode;
use Illuminate\Foundation\Auth\User as Authenticatable;

/** Tabel t_pengguna (akun seluruh pengguna sistem). */
class Pengguna extends Authenticatable
{
    protected $table = 't_pengguna';
    protected $primaryKey = 'id_pengguna';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public const ROLE_ADMIN = 'admin_wr3';
    public const ROLE_STAF = 'staf_beasiswa';
    public const ROLE_MAHASISWA = 'mahasiswa';
    public const ROLE_MITRA = 'mitra';

    public const LABEL_ROLE = [
        self::ROLE_ADMIN => 'Admin WR3',
        self::ROLE_STAF => 'Staf Unit Beasiswa',
        self::ROLE_MAHASISWA => 'Mahasiswa',
        self::ROLE_MITRA => 'Mitra / Donor',
    ];

    protected $fillable = [
        'id_pengguna', 'username', 'password', 'nama', 'email', 'role', 'status_aktif', 'last_login',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'password' => 'hashed',
        'status_aktif' => 'boolean',
        'last_login' => 'datetime',
    ];

    /** Fitur "ingat saya" tidak dipakai (SKPL tidak mendefinisikan kolom remember_token). */
    public function getRememberTokenName()
    {
        return '';
    }

    protected static function booted(): void
    {
        static::creating(function (self $p) {
            if (empty($p->id_pengguna)) {
                $p->id_pengguna = Kode::berikutnya('t_pengguna', 'id_pengguna', 'US', 5);
            }
        });
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /** Admin WR3 adalah generalisasi Staf Unit Beasiswa (SKPL 2.2.1). */
    public function isStaf(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_STAF], true);
    }

    public function isMahasiswa(): bool
    {
        return $this->role === self::ROLE_MAHASISWA;
    }

    public function isMitra(): bool
    {
        return $this->role === self::ROLE_MITRA;
    }

    public function getLabelRoleAttribute(): string
    {
        return self::LABEL_ROLE[$this->role] ?? $this->role;
    }

    public function mahasiswa()
    {
        return $this->hasOne(Mahasiswa::class, 'id_pengguna', 'id_pengguna');
    }

    public function mitra()
    {
        return $this->hasOne(Mitra::class, 'id_pengguna', 'id_pengguna');
    }

    public function notifikasi()
    {
        return $this->hasMany(Notifikasi::class, 'id_pengguna', 'id_pengguna');
    }
}
