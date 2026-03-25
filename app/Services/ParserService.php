<?php

namespace App\Services;

use App\Models\Site;
use App\Services\Parsers\StaticHtmlParser;

class ParserService
{
    public function __construct(private readonly StaticHtmlParser $staticHtmlParser)
    {
    }

    public function parseSite(Site $site): array
    {
        if (blank($site->local_repo_path) || ! is_dir($site->local_repo_path)) {
            return [];
        }

        return match ($site->project_type) {
            'static_html' => $this->staticHtmlParser->parse($site->local_repo_path),
            default => [],
        };
    }
}
