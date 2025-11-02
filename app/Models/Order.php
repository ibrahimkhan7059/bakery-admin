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
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'delivery_address',
        'delivery_type',
        'city',
        'status',
        'total_amount',
        'subtotal',
        'delivery_charges',
        'payment_status',
        'payment_method',
        'payment_receipt',
        'delivery_time',
        'estimated_delivery_time',
        'priority',
        'notes',
        'special_notes',
        // Payment related
        'basket_id',
        'payment_status_payfast',
        'payment_method_type',
        'transaction_id',
        'payment_error',
        'payment_date'
    ];

    protected $casts = [
        'delivery_time' => 'datetime',
        'estimated_delivery_time' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_items')
            ->withPivot('quantity', 'price', 'discount', 'subtotal', 'product_name')
            ->withTimestamps();
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

    public function scopeReady(Builder $query)
    {
        return $query->where('status', 'ready');
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
            'ready' => '🟣 Ready',
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
        $total = 0;
        foreach ($this->products as $product) {
            $quantity = $product->pivot->quantity;
            $price = $product->pivot->price;
            $discount = $product->pivot->discount ?? 0;
            
            // Apply bulk discount if applicable
            if ($quantity >= 10) {
                $discount = max($discount, 0.10); // 10% discount for 10+ items
            } elseif ($quantity >= 5) {
                $discount = max($discount, 0.05); // 5% discount for 5+ items
            }
            
            $total += ($price * $quantity) * (1 - $discount);
        }
        return $total;
    }

    public function updateStock()
    {
        foreach ($this->products as $product) {
            $quantity = $product->pivot->quantity;
            $product->decrement('stock', $quantity);
        }
    }

    public function estimateDeliveryTime()
    {
        // Base delivery time: 30 minutes
        $baseTime = now()->addMinutes(30);
        
        // Add time based on order size
        $totalItems = $this->products->sum('pivot.quantity');
        if ($totalItems > 20) {
            $baseTime->addMinutes(15);
        } elseif ($totalItems > 10) {
            $baseTime->addMinutes(10);
        }
        
        $this->estimated_delivery_time = $baseTime;
        $this->save();
    }

    public function generateReceipt()
    {
        $receipt = [
            'order_id' => $this->id,
            'date' => $this->created_at->format('Y-m-d H:i:s'),
            'customer' => $this->user->name,
            'items' => [],
            'subtotal' => 0,
            'discount' => 0,
            'total' => $this->total_amount,
            'payment_status' => $this->payment_status,
            'estimated_delivery' => $this->estimated_delivery_time->format('Y-m-d H:i:s'),
        ];

        foreach ($this->products as $product) {
            $quantity = $product->pivot->quantity;
            $price = $product->pivot->price;
            $discount = $product->pivot->discount ?? 0;
            $itemTotal = ($price * $quantity) * (1 - $discount);

            $receipt['items'][] = [
                'name' => $product->name,
                'quantity' => $quantity,
                'price' => $price,
                'discount' => $discount * 100 . '%',
                'total' => $itemTotal,
            ];

            $receipt['subtotal'] += $price * $quantity;
            $receipt['discount'] += ($price * $quantity) * $discount;
        }

        return $receipt;
    }
}

