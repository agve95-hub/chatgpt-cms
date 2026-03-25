<?php

namespace App\Http\Controllers;

use App\Jobs\CloneSiteRepository;
use App\Models\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function store(Request $request)
    {
        $site = Site::create([
            'name' => (string) $request->input('name'),
            'repo_url' => (string) $request->input('repo_url'),
            'repo_branch' => (string) ($request->input('repo_branch') ?: 'main'),
            'status' => 'queued',
        ]);

        CloneSiteRepository::dispatch($site->id);

        return redirect()->route('sites.index');
    }
}
