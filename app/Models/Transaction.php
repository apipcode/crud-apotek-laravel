<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_date',
        'total_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'total_amount' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Transaction $transaction): void {
            if (blank($transaction->transaction_date)) {
                $transaction->transaction_date = now();
            }
        });

        static::created(function (Transaction $transaction): void {
            $transaction->loadMissing('items.medicine');

            foreach ($transaction->items as $item) {
                if (! $item->medicine) {
                    continue;
                }

                $item->medicine->decrement('stock', $item->quantity);
            }
        });

        static::deleting(function (Transaction $transaction): void {
            $transaction->loadMissing('items.medicine');

            foreach ($transaction->items as $item) {
                if (! $item->medicine) {
                    continue;
                }

                $item->medicine->increment('stock', $item->quantity);
            }
        });
    }
}
