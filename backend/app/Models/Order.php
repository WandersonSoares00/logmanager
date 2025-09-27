<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'meli_order_id',
        'meli_account_id',
        'status',
        'total_amount',
        'shipping_id',
        'shipping_label_url',
        'shipping_label_local_path',
        'customer_data',
        'paid_at',
        'ready_to_ship_at',
        'shipped_at',
    ];

    protected $casts = [
        'customer_data' => 'array',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'ready_to_ship_at' => 'datetime',
        'shipped_at' => 'datetime',
    ];

    public function meliAccount(): BelongsTo
    {
        return $this->belongsTo(MeliAccount::class);
    }

    protected $appends = ['label_download_url'];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class);
    }

    protected function labelDownloadUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->shipping_label_local_path
                ? route('orders.label.show', $this)
                : null,
        );
    }
}
