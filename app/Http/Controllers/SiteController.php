<?php

namespace App\Http\Controllers;

use App\Jobs\CloneSiteRepository;
use App\Models\Site;
use App\Services\RepoUrlNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function __construct(private readonly RepoUrlNormalizer $repoUrlNormalizer)
    {
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'repo_url' => ['required', 'url', 'max:2048'],
            'repo_branch' => ['nullable', 'string', 'max:120'],
        ]);

        $site = Site::create([
            'name' => $validated['name'],
            'repo_url' => $this->repoUrlNormalizer->normalize($validated['repo_url']),
            'repo_branch' => $validated['repo_branch'] ?: 'main',
            'status' => 'queued',
        ]);

        CloneSiteRepository::dispatch($site->id);

        return redirect()->route('sites.index')->with('status', 'Site queued for clone and project detection.');
    }
}
