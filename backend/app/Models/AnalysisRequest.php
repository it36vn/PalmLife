<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'type', 'locale', 'input_hash', 'result', 'disclaimer_acknowledged_at'])]
class AnalysisRequest extends Model
{
    protected function casts(): array
    {
        return [
            'result' => 'array',
            'disclaimer_acknowledged_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
