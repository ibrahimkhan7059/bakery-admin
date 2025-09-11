<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'image',
        'category_id',
        'allergens',
        'alternative_product_id'
    ];
    
    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'allergens' => 'string', // changed from 'json' to 'string'
        'alternative_product_id' => 'integer',
    ];
    
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            \Log::info('No image set for product', ['id' => $this->id]);
            return asset('images/no-image.png');
        }
        
        // Check if the image exists in storage
        if (Storage::disk('public')->exists($this->image)) {
            $url = Storage::url($this->image);
            \Log::info('Image URL generated', [
                'id' => $this->id,
                'path' => $this->image,
                'url' => $url
            ]);
            return $url;
        }
        
        \Log::warning('Image not found in storage', [
            'id' => $this->id,
            'path' => $this->image
        ]);
        return asset('images/no-image.png');
    }
}
