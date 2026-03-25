<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deploy extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'trigger',
        'status',
        'commit_sha',
        'duration_ms',
        'log_path',
        'meta',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
