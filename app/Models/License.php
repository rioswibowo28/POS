<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class License extends Model
{
    use SoftDeletes;

    /**
     * Note: start_date & expiry_date adalah masa aktif AKTIVASI di POS-RESTO
     * Bukan masa berlaku license di LICENSE-MANAGER
     * Dihitung sejak aktivasi berdasarkan license_type:
     * - trial: 14 hari dari aktivasi
     * - monthly: 1 bulan dari aktivasi
     * - yearly: 1 tahun dari aktivasi
     * - lifetime: tanpa expiry
     */

    protected $fillable = [
        'license_key',
        'customer_name',
        'customer_email',
        'customer_phone',
        'business_name',
        'status',
        'license_type',
        'start_date',
        'expiry_date',
        'max_users',
        'max_tables',
        'activated_by_ip',
        'activated_at',
        'last_checked_at',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expiry_date' => 'date',
        'activated_at' => 'datetime',
        'last_checked_at' => 'datetime',
    ];

    /**
     * Check if license is active and not expired
     */
    public function isValid(): bool
    {
        if ($this->status === 'suspended') {
            return false;
        }

        if ($this->expiry_date && $this->expiry_date->isPast()) {
            $this->update(['status' => 'expired']);
            return false;
        }

        return $this->status === 'active';
    }

    /**
     * Get days remaining until expiry
     * Matches LICENSE-MANAGER calculation (includes expiry day)
     */
    public function daysRemaining(): int
    {
        if (!$this->expiry_date) {
            return 999999; // Lifetime
        }

        $today = now()->startOfDay();
        $expiry = $this->expiry_date->copy()->startOfDay();

        $diffDays = (int) $today->diffInDays($expiry, false);

        return max(0, $diffDays);
    }

    /**
     * Check if license is expiring soon (within 7 days)
     */
    public function isExpiringSoon(): bool
    {
        return $this->daysRemaining() <= 7 && $this->daysRemaining() > 0;
    }

    /**
     * Generate a unique license key
     */
    public static function generateKey(): string
    {
        do {
            $key = strtoupper(sprintf(
                '%s-%s-%s-%s',
                substr(md5(uniqid()), 0, 4),
                substr(md5(uniqid()), 0, 4),
                substr(md5(uniqid()), 0, 4),
                substr(md5(uniqid()), 0, 4)
            ));
        } while (self::where('license_key', $key)->exists());

        return $key;
    }

    /**
     * Get current active license
     */
    public static function current(): ?self
    {
        return self::where('status', 'active')
            ->where(function($query) {
                $query->whereNull('expiry_date')
                      ->orWhere('expiry_date', '>=', now());
            })
            ->orderBy('activated_at', 'desc')
            ->first();
    }
}
