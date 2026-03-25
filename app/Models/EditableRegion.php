<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EditableRegion extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_id',
        'selector',
        'marker_id',
        'region_type',
        'is_static',
        'detection_method',
        'confidence_score',
        'current_content',
        'source_location',
    ];

    protected $casts = [
        'is_static' => 'boolean',
        'confidence_score' => 'float',
        'source_location' => 'array',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
