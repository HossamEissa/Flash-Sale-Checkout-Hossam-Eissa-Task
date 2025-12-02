<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Str;

class ProductObserver
{
    /**
     * Handle the Product "creating" event.
     */
    public function creating(Product $product): void
    {
        if (empty($product->slug)) {
            $product->slug = $this->generateUniqueSlug($product->name);
        }
    }

    /**
     * Handle the Product "updating" event.
     */
    public function updating(Product $product): void
    {
        // Only regenerate slug if name changed and slug is empty
        if ($product->isDirty('name') && empty($product->slug)) {
            $product->slug = $this->generateUniqueSlug($product->name);
        }
    }

    /**
     * Generate a unique slug for the product
     */
    private function generateUniqueSlug(string $name, int $attempt = 0): string
    {
        $baseSlug = Str::slug($name);
        $slug = $attempt > 0 ? "{$baseSlug}-{$attempt}" : $baseSlug;
        
        // Check if slug exists
        if (Product::where('slug', $slug)->exists()) {
            return $this->generateUniqueSlug($name, $attempt + 1);
        }
        
        return $slug;
    }
}