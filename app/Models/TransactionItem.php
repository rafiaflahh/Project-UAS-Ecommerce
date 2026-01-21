<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quantity',
        'unit_price',
        'subtotal',
        'transaction_id',
        'product_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getFormattedUnitPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->unit_price, 0, ',', '.');
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    protected static function booted()
    {
        static::creating(function ($item) {
            $item->subtotal = $item->quantity * $item->unit_price;
        });

        static::updating(function ($item) {
            if ($item->isDirty('quantity') || $item->isDirty('unit_price')) {
                $item->subtotal = $item->quantity * $item->unit_price;
            }
        });
    }
}
