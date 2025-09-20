<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden   = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // IZINKAN role-role berikut mengakses panel /admin
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole([
            'admin_produksi',
            'admin_produksi_fitting',
            'admin_produksi_flange',
            'supervisor_qa',
            'hr',
            'viewer',
        ]);
    }
}
