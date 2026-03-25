<?php

namespace App\Services;

class RegionDetector
{
    public function detect(array $nodes): array
    {
        return collect($nodes)
            ->map(function (array $node): array {
                $score = 0.0;
                $tag = strtolower((string) ($node['tag'] ?? ''));
                $text = trim((string) ($node['text'] ?? ''));
                $attributes = $node['attributes'] ?? [];

                if (in_array($tag, ['h1', 'h2', 'h3', 'p', 'article', 'section', 'figcaption'], true)) {
                    $score += 0.3;
                }

                if (mb_strlen($text) > 20) {
                    $score += 0.2;
                }

                if (($node['inside_main'] ?? false) === true) {
                    $score += 0.2;
                }

                if ($tag === 'img' && isset($attributes['src'])) {
                    $score += 0.1;
                }

                if ($tag === 'a' && $text !== '') {
                    $score += 0.1;
                }

                if (($node['inside_layout'] ?? false) === true) {
                    $score -= 0.3;
                }

                if (($node['repeats_across_pages'] ?? false) === true) {
                    $score -= 0.2;
                }

                return [
                    'selector' => $node['selector'] ?? null,
                    'region_type' => $this->inferRegionType($tag),
                    'is_static' => $score < 0.5,
                    'detection_method' => 'auto',
                    'confidence_score' => max(0, min(1, $score)),
                    'current_content' => $node['html'] ?? $text,
                    'source_location' => $node['source_location'] ?? null,
                ];
            })
            ->all();
    }

    private function inferRegionType(string $tag): string
    {
        return match ($tag) {
            'img' => 'image',
            'a' => 'link',
            'section', 'article' => 'section',
            'ul', 'ol' => 'list',
            default => 'text',
        };
    }
}
