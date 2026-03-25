<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Site extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'repo_url',
        'repo_provider',
        'repo_branch',
        'local_repo_path',
        'project_type',
        'build_command',
        'build_output_dir',
        'deploy_path',
        'status',
        'github_webhook_id',
        'last_synced_at',
        'meta',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
        'meta' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Site $site): void {
            if (blank($site->slug)) {
                $site->slug = Str::slug($site->name);
            }
        });
    }

    public function deploys(): HasMany
    {
        return $this->hasMany(Deploy::class);
    }
}
