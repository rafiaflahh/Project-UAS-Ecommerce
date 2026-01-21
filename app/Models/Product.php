<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'cost_price',
        'sku',
        'image_url',
        'is_active',
        'category_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class)->select(['id', 'name', 'slug']);
    }

    public function stock()
    {
        return $this->hasOne(ProductStock::class)->select(['id', 'product_id', 'quantity', 'reserved_quantity']);
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class)
            ->select(['id', 'transaction_id', 'product_id', 'quantity', 'unit_price', 'subtotal']);
    }

    // Optimized eager loading scopes
    public function scopeWithRelations($query)
    {
        return $query->with(['category', 'stock']);
    }

    public function scopeWithCategory($query)
    {
        return $query->with('category:id,name,slug');
    }

    public function scopeWithStock($query)
    {
        return $query->with('stock:id,product_id,quantity,reserved_quantity');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->whereHas('stock', function ($stockQuery) {
            $stockQuery->whereRaw('quantity > reserved_quantity');
        });
    }

    public function scopeLowStock($query, $threshold = 5)
    {
        return $query->whereHas('stock', function ($stockQuery) use ($threshold) {
            $stockQuery->whereRaw('(quantity - reserved_quantity) <= ?', [$threshold]);
        });
    }

    // Search scopes
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhere('sku', 'like', "%{$term}%");
        });
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getFormattedCostPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->cost_price ?? 0, 0, ',', '.');
    }

    public function getProfitMarginAttribute(): float
    {
        if (!$this->cost_price || $this->price <= 0) return 0;
        return (($this->price - $this->cost_price) / $this->price) * 100;
    }

    public function getProfitAmountAttribute(): float
    {
        if (!$this->cost_price) return 0;
        return $this->price - $this->cost_price;
    }

    public function getAvailableStockAttribute(): int
    {
        return $this->stock ? $this->stock->quantity - $this->stock->reserved_quantity : 0;
    }

    public function isInStock(): bool
    {
        return $this->getAvailableStockAttribute() > 0;
    }
}
