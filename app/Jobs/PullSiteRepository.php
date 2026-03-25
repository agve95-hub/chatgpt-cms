<?php

namespace App\Jobs;

use App\Models\Site;
use App\Services\GitSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PullSiteRepository implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $siteId)
    {
    }

    public function handle(GitSyncService $gitSyncService): void
    {
        $site = Site::findOrFail($this->siteId);

        if (blank($site->local_repo_path)) {
            return;
        }

        $gitSyncService->pull($site);

        $meta = $site->meta ?? [];
        $meta['last_commit'] = $gitSyncService->currentCommit($site->local_repo_path);

        $site->update([
            'last_synced_at' => now(),
            'meta' => $meta,
        ]);
    }
}
