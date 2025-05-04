<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bulk_order_id',
        'product_id',
        'product_name',
        'quantity',
        'price',
        'discount',
        'notes'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'discount' => 'decimal:2'
    ];

    public function bulkOrder(): BelongsTo
    {
        return $this->belongsTo(BulkOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getSubtotalAttribute()
    {
        return $this->price * $this->quantity;
    }

    public function getDiscountAmountAttribute()
    {
        return $this->subtotal * $this->discount;
    }

    public function getTotalAttribute()
    {
        return $this->subtotal - $this->discount_amount;
    }
} 