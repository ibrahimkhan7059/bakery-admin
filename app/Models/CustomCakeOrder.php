<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomCakeOrder extends Model
{
    protected $fillable = [
        'user_id',
        'cake_size',
        'cake_flavor',
        'cake_filling',
        'cake_frosting',
        'special_instructions',
        'reference_image',
        'price',
        'delivery_date',
        'delivery_address',
        'status',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'price' => 'decimal:2',
    ];

    // Relationship with User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Get formatted status
    public function getFormattedStatusAttribute()
    {
        return ucfirst($this->status);
    }

    // Get formatted price
    public function getFormattedPriceAttribute()
    {
        return 'PKR ' . number_format($this->price, 2);
    }

    // Status badge color for UI
    public function getStatusBadgeColorAttribute()
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'processing' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    // Get reference image URL
    public function getReferenceImageUrlAttribute()
    {
        return $this->reference_image ? asset('storage/' . $this->reference_image) : null;
    }
}
