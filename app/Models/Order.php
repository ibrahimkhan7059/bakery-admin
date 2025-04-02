<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    

    

    protected $fillable = ['customer_name','customer_phone', 'product', 'quantity', 'price', 'total_price', 'status'];
    
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

}

