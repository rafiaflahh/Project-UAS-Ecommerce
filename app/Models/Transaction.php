<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'total_amount',
        'status',
        'notes',
        'address',
        'payment_method',
        'user_id',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'status' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'PENDING' => 'yellow',
            'PROCESSING' => 'blue',
            'COMPLETED' => 'green',
            'CANCELLED' => 'red',
            default => 'gray',
        };
    }

    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'PROCESSING';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'COMPLETED';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'CANCELLED';
    }

    protected static function booted()
    {
        static::creating(function ($transaction) {
            $transaction->transaction_number = 'TXN-' . date('Ymd') . '-' . str_pad(Transaction::count() + 1, 4, '0', STR_PAD_LEFT);
        });
    }
}
