<?php

namespace App\Projects\Inventario\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasUuids, HasApiTokens;

    protected $connection = 'inventario';
    protected $table = 'users';

    protected $fillable = [
        'email',
        'password_hash',
        'first_name',
        'last_name',
        'avatar_url',
        'email_verified',
        'status',
        'last_login_at',
    ];

    protected $casts = [
        'email_verified' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    protected $hidden = [
        'password_hash',
    ];

    /**
     * Obtener el campo de contraseña para autenticación
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Obtener las organizaciones del usuario
     */
    public function organizations(): HasMany
    {
        return $this->hasMany(OrganizationMember::class);
    }
}
