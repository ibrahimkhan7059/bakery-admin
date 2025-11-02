<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FcmToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token',
        'platform',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the FCM token
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get active tokens only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get tokens for a specific platform
     */
    public function scopePlatform($query, $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * Get active tokens for a user
     */
    public static function getActiveTokensForUser($userId)
    {
        return static::where('user_id', $userId)
                    ->where('is_active', true)
                    ->pluck('token')
                    ->toArray();
    }

    /**
     * Register a new token for a user
     */
    public static function registerToken($userId, $token, $platform = 'android')
    {
        return static::updateOrCreate(
            [
                'user_id' => $userId,
                'token' => $token,
            ],
            [
                'platform' => $platform,
                'is_active' => true,
            ]
        );
    }

    /**
     * Deactivate a token
     */
    public static function deactivateToken($token)
    {
        return static::where('token', $token)->update(['is_active' => false]);
    }

    /**
     * Remove invalid tokens
     */
    public static function removeInvalidTokens(array $tokens)
    {
        return static::whereIn('token', $tokens)->delete();
    }
}
