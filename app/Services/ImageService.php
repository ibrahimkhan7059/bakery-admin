<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ImageService
{
    protected $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    public function handleProductImage(UploadedFile $image, $productId = null)
    {
        // Generate unique filename
        $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        
        // Resize and save image
        $resizedImage = $this->manager->read($image->getRealPath())
                                    ->resize(500, 500, function ($constraint) {
                                        $constraint->aspectRatio();
                                        $constraint->upsize();
                                    })
                                    ->toJpeg(75);

        $path = "products/{$filename}";
        Storage::put("public/{$path}", $resizedImage);

        // If updating product, delete old image
        if ($productId) {
            $this->deleteProductImage($productId);
        }

        return $path;
    }

    public function deleteProductImage($productId)
    {
        $product = \App\Models\Product::find($productId);
        if ($product && $product->image) {
            if (Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
        }
    }

    // Alias method for backward compatibility
    public function deleteProductImages($productId)
    {
        return $this->deleteProductImage($productId);
    }

    public function getImageUrl($path, $size = 'medium')
    {
        return Storage::url($path);
    }
} 