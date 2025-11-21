<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'token',
        'token_expires_at',
        'last_active_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function markActive()
    {
        $this->update(['last_active_at' => now()]);
    }

    public function isActive(): bool
    {
        return $this->last_active_at &&
            $this->last_active_at->greaterThan(now()->subMinutes(10));
    }

    public function isTokenValid(): bool
    {
        return $this->token && $this->token_expires_at > now();
    }

    public function clearToken()
    {
        $this->update([
            'token' => null,
            'token_expires_at' => null,
        ]);
    }

    public function generateToken(): string
    {
        $token = 'tok_' . bin2hex(random_bytes(32));

        $this->token = $token;
        $this->token_expires_at = now()->addHours(4);
        $this->save();

        return $token;
    }

    public function dailyActivities()
    {
        return $this->hasMany(UserDailyActivity::class);
    }

    public function logDailyActivity(): void
    {
        // APP_TIMEZONE sudah Asia/Jakarta, jadi now() otomatis pakai zona itu
        $today = now()->toDateString(); // contoh: 2025-01-21

        UserDailyActivity::firstOrCreate([
            'user_id'       => $this->id,
            'activity_date' => $today,
        ]);
    }

    public function markActiveAndLog(): void
    {
        $this->update(['last_active_at' => now()]);
        $this->logDailyActivity();
    }



}
