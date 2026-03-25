<?php

namespace App\Jobs;

use App\Models\Site;
use App\Services\GitSyncService;
use App\Services\ProjectDetector;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CloneSiteRepository implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $siteId)
    {
    }

    public function handle(GitSyncService $gitSyncService, ProjectDetector $projectDetector): void
    {
        $site = Site::findOrFail($this->siteId);
        $site->update(['status' => 'cloning']);

        $repoPath = $gitSyncService->clone($site);
        $detected = $projectDetector->detect($repoPath);

        $site->update([
            'local_repo_path' => $repoPath,
            'project_type' => $detected['project_type'],
            'build_command' => $detected['build_command'],
            'build_output_dir' => $detected['build_output_dir'],
            'status' => 'synced',
            'last_synced_at' => now(),
            'meta' => [
                'last_commit' => $gitSyncService->currentCommit($repoPath),
            ],
        ]);

        ParseSiteContent::dispatch($site->id);
    }
}
