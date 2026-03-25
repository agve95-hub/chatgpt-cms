<?php

namespace App\Services;

use App\Models\Site;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use RuntimeException;

class GitSyncService
{
    public function repoRoot(): string
    {
        return rtrim(config('pixelkraft.repo_root'), '/');
    }

    public function repoPathForSlug(string $slug): string
    {
        return $this->repoRoot() . '/' . Str::slug($slug);
    }

    public function clone(Site $site): string
    {
        $path = $this->repoPathForSlug($site->slug);

        if (is_dir($path . '/.git')) {
            return $path;
        }

        $result = Process::path($this->repoRoot())->run([
            'git', 'clone', '--branch', $site->repo_branch, $site->repo_url, $path,
        ]);

        if ($result->failed()) {
            throw new RuntimeException('Git clone failed: ' . $result->errorOutput());
        }

        return $path;
    }

    public function pull(Site $site): void
    {
        $result = Process::path($site->local_repo_path)->run(['git', 'pull', 'origin', $site->repo_branch]);

        if ($result->failed()) {
            throw new RuntimeException('Git pull failed: ' . $result->errorOutput());
        }
    }

    public function currentCommit(string $repoPath): ?string
    {
        $result = Process::path($repoPath)->run(['git', 'rev-parse', 'HEAD']);

        return $result->successful() ? trim($result->output()) : null;
    }
}
