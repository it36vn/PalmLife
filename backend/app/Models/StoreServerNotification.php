<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['platform', 'event_id', 'event_type', 'product_id', 'purchase_token', 'transaction_id', 'status', 'payload', 'processed_at'])]
class StoreServerNotification extends Model
{
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }
}
