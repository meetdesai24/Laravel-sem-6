<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ADMIN = 'admin';
    public const SECURITY = 'security';
    public const HOST = 'host';
    public const ROLES = [self::ADMIN, self::SECURITY, self::HOST];

    protected $fillable = ['name', 'email', 'phone', 'password', 'role', 'department', 'status'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'status'            => 'boolean',
        ];
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function hostedVisits(): HasMany
    {
        return $this->hasMany(Visit::class, 'host_id');
    }
}
