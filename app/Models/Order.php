<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;
    

    

    protected $fillable = [
        'customer_name',
        'customer_phone',
        'delivery_address',
        'notes',
        'status',
        'order_date',
        'payment_method',
        'payment_status',
        'priority',
        'total_price'
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'total_price' => 'decimal:2',
        'priority' => 'integer'
    ];

    // Relationships
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Scopes for filtering
    public function scopePending(Builder $query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing(Builder $query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted(Builder $query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled(Builder $query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeDateRange(Builder $query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeSearch(Builder $query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('customer_name', 'like', "%{$search}%")
              ->orWhere('customer_phone', 'like', "%{$search}%")
              ->orWhere('delivery_address', 'like', "%{$search}%");
        });
    }

    public function scopePriority(Builder $query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function getFormattedStatusAttribute()
    {
        $statuses = [
            'pending' => '🔴 Pending',
            'processing' => '🔵 Processing',
            'completed' => '✅ Completed',
            'cancelled' => '❌ Cancelled',
        ];

        return $statuses[$this->status] ?? 'Unknown';
    }

    public function getFormattedPaymentStatusAttribute()
    {
        $statuses = [
            'pending' => '🔴 Pending',
            'paid' => '✅ Paid',
            'failed' => '❌ Failed',
            'refunded' => '↩️ Refunded',
        ];

        return $statuses[$this->payment_status] ?? 'Unknown';
    }

    public function getFormattedPriorityAttribute()
    {
        $priorities = [
            1 => '🔴 High',
            2 => '🟡 Medium',
            3 => '🟢 Low',
        ];

        return $priorities[$this->priority] ?? 'Unknown';
    }

    public function getFormattedOrderDateAttribute()
    {
        return $this->created_at->format('M d, Y h:i A');
    }

    public function calculateTotal()
    {
        return $this->items->sum(function($item) {
            return $item->quantity * $item->price;
        });
    }
}

