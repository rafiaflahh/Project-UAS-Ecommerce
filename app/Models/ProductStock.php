<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'quantity',
        'reserved_quantity',
        'product_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getAvailableQuantityAttribute(): int
    {
        return $this->quantity - $this->reserved_quantity;
    }

    public function reserveStock(int $amount): bool
    {
        if ($this->getAvailableQuantityAttribute() >= $amount) {
            $this->reserved_quantity += $amount;
            return $this->save();
        }
        return false;
    }

    public function releaseStock(int $amount): bool
    {
        if ($this->reserved_quantity >= $amount) {
            $this->reserved_quantity -= $amount;
            return $this->save();
        }
        return false;
    }

    public function confirmStock(int $amount): bool
    {
        if ($this->reserved_quantity >= $amount) {
            $this->quantity -= $amount;
            $this->reserved_quantity -= $amount;
            return $this->save();
        }
        return false;
    }
}
