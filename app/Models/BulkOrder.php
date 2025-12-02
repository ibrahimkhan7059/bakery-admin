<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'customer_name',
        'customer_phone',
        'customer_email',
        'delivery_address',
        'delivery_date',
        'delivery_time',
        'order_type',
        'event_details',
        'total_amount',
        'status',
        'payment_status',
        'payment_method',
        'advance_payment',
        'special_instructions',
        'cancellation_reason',
        'user_id',
        'basket_id',
        'transaction_id',
        'payment_date',
        'payment_error'
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'delivery_time' => 'datetime',
        'total_amount' => 'decimal:2',
        'advance_payment' => 'decimal:2'
    ];

    public function items(): HasMany
    {
        return $this->hasMany(BulkOrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'bulk_order_items')
            ->withPivot(['quantity', 'price', 'discount', 'notes'])
            ->withTimestamps();
    }

    public function calculateTotal()
    {
        return $this->items->sum(function ($item) {
            return ($item->price * $item->quantity) * (1 - $item->discount);
        });
    }

    public function getRemainingPaymentAttribute()
    {
        return $this->total_amount - $this->advance_payment;
    }

    public function getFormattedStatusAttribute()
    {
        return ucfirst($this->status);
    }

    public function getFormattedPaymentStatusAttribute()
    {
        return ucfirst($this->payment_status);
    }

    public function getFormattedOrderTypeAttribute()
    {
        return ucfirst($this->order_type);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bulkOrder) {
            $bulkOrder->order_number = 'BULK-' . strtoupper(uniqid());
        });
    }
} 