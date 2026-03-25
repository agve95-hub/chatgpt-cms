<?php

namespace App\Jobs;

use App\Models\Page;
use App\Models\Site;
use App\Services\ParserService;
use App\Services\RegionDetector;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ParseSiteContent implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $siteId)
    {
    }

    public function handle(ParserService $parserService, RegionDetector $regionDetector): void
    {
        $site = Site::findOrFail($this->siteId);
        $site->update(['status' => 'parsing']);

        $parsedPages = $parserService->parseSite($site);

        foreach ($parsedPages as $parsedPage) {
            /** @var Page $page */
            $page = Page::query()->updateOrCreate(
                [
                    'site_id' => $site->id,
                    'file_path' => $parsedPage['file_path'],
                ],
                [
                    'url_path' => $parsedPage['url_path'],
                    'title' => $parsedPage['title'],
                    'meta_description' => $parsedPage['meta_description'],
                    'content_hash' => $parsedPage['content_hash'],
                    'is_published' => true,
                ]
            );

            $regions = $regionDetector->detect($parsedPage['nodes']);

            $page->regions()->delete();
            $page->regions()->createMany($regions);
        }

        $site->update(['status' => 'ready']);
    }
}
