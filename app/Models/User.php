<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail; // <-- Activé pour la vérification par e-mail
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail // <-- Ajout de l'implémentation ici
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role_id',
        'agency_id',
        'status',
        'last_login_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if (!$this->role) {
            return false;
        }

        if ($this->role->slug === 'admin') {
            return true;
        }

        return $this->role->permissions->contains('slug', $permissionSlug);
    }

    public function hasModule(string $moduleName): bool
    {
        if (!$this->role) {
            return false;
        }

        if ($this->role->slug === 'admin') {
            return true;
        }

        return $this->role->permissions->contains('module', $moduleName);
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function vipClient(): HasOne
    {
        return $this->hasOne(VipClient::class);
    }
}
