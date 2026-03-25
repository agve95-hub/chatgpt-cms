<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'file_path',
        'url_path',
        'title',
        'meta_description',
        'meta_keywords',
        'og_title',
        'og_description',
        'og_image',
        'canonical_url',
        'schema_json',
        'seo_score',
        'lighthouse_score',
        'screenshot_url',
        'content_hash',
        'is_published',
    ];

    protected $casts = [
        'schema_json' => 'array',
        'lighthouse_score' => 'array',
        'is_published' => 'boolean',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function regions(): HasMany
    {
        return $this->hasMany(EditableRegion::class);
    }
}
