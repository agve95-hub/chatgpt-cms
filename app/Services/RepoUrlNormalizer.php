<?php

namespace App\Services;

class RepoUrlNormalizer
{
    public function normalize(string $url): string
    {
        $trimmed = trim($url);

        if ($trimmed === '') {
            return $trimmed;
        }

        if (preg_match('/^git@github\.com:(.+?)(?:\.git)?$/i', $trimmed, $matches) === 1) {
            $trimmed = 'https://github.com/' . $matches[1];
        }

        $parts = parse_url($trimmed);

        if (! is_array($parts)) {
            return rtrim(preg_replace('/\.git$/i', '', $trimmed) ?? $trimmed, '/');
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? 'https'));
        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = (string) ($parts['path'] ?? '');

        $path = preg_replace('/\.git$/i', '', $path) ?? $path;
        $path = '/' . ltrim($path, '/');
        $path = rtrim($path, '/');

        return "{$scheme}://{$host}{$path}";
    }
}

