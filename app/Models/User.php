<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'user';

    protected $fillable = [
        'username',
        'password',
        'nama',
        'nik',
        'email',
        'no_hp',
        'online',
        'status',
        'jabatan',
        'level',
        'otp',
        'time_otp',
        'authorized_menus', 
        'roles'
    ];

    protected $hidden = [
        'password',
        'otp',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'online' => 'boolean',
            'time_otp' => 'datetime',
            'authorized_menus' => 'array',
            'roles' => 'array',
        ];
    }

    /**
     * Helper method untuk mendapatkan roles sebagai array
     */
    public function getRolesArray()
    {
        if (is_array($this->roles)) {
            return $this->roles;
        }
        return json_decode($this->roles ?? '{}', true) ?: [];
    }

    /**
     * Helper method untuk mendapatkan authorized_menus sebagai array
     */
    public function getAuthorizedMenusArray()
    {
        if (is_array($this->authorized_menus)) {
            return $this->authorized_menus;
        }
        return json_decode($this->authorized_menus ?? '[]', true) ?: [];
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get list of status options
     */
    public static function statusList()
    {
        return [
            'active' => 'Active',
            'inactive' => 'Inactive',
        ];
    }

    /**
     * Get list of level options
     */
    public static function levelList()
    {
        return [
            '0' => '0. Bukan Komite',
            '1' => '1. Keputusan',
            '2' => '2. Mengetahui',
            '3' => '3. Opini',
            '4' => '4. Rekomendasi',
        ];
    }

    /**
     * Get status label attribute
     */
    public function getStatusLabelAttribute()
    {
        if (!$this->status) {
            return null;
        }
        $list = self::statusList();
        return $list[$this->status] ?? $this->status;
    }

    /**
     * Get level label attribute
     */
    public function getLevelLabelAttribute()
    {
        if ($this->level === null || $this->level === '') {
            return null;
        }
        $list = self::levelList();
        return $list[(string)$this->level] ?? $this->level;
    }
}
