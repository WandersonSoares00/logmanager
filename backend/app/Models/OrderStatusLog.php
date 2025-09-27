<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusLog extends Model
{
    use HasFactory;

    /** A tabela só tem 'created_at' */
    const UPDATED_AT = null;

    protected $fillable = [
        'order_id',
        'status',
        'log_data',
    ];

    protected $casts = [
        'log_data' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
