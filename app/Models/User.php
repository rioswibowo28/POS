<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'password' => 'hashed',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    // Relationships
    public function orders()
    {
        return $this->hasMany(Order::class, 'cashier_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'processed_by');
    }
    
    // Helper methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }
    
    public function isCashier()
    {
        return $this->role === 'cashier';
    }

    public function isTaxDevice()
    {
        return $this->role === 'tax_device';
    }

    public function canAccessReports()
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isCashier()) {
            return setting('cashier_can_access_reports', '0') == '1';
        }

        return false;
    }
}
