<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResearchProject extends Model
{
    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'description',
        'status',
        'protocol_metadata',
    ];

    protected function casts(): array
    {
        return ['protocol_metadata' => 'array'];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
