<?php

namespace App\Services\Parsers;

class StaticHtmlParser
{
    public function parse(string $repoPath): array
    {
        $pages = [];

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($repoPath));

        foreach ($iterator as $fileInfo) {
            if (! $fileInfo instanceof \SplFileInfo || ! $fileInfo->isFile() || strtolower($fileInfo->getExtension()) !== 'html') {
                continue;
            }

            $filePath = $fileInfo->getPathname();

            $contents = (string) file_get_contents($filePath);
            $dom = new \DOMDocument();
            @$dom->loadHTML($contents);

            $relativePath = ltrim(str_replace($repoPath, '', $filePath), '/');
            $urlPath = $this->toUrlPath($relativePath);

            $pages[] = [
                'file_path' => $relativePath,
                'url_path' => $urlPath,
                'title' => $this->extractTitle($dom),
                'meta_description' => $this->extractMeta($dom, 'description'),
                'content_hash' => hash('sha256', $contents),
                'nodes' => $this->extractNodes($dom),
            ];
        }

        return $pages;
    }

    private function extractTitle(\DOMDocument $dom): ?string
    {
        $titles = $dom->getElementsByTagName('title');

        if ($titles->length === 0) {
            return null;
        }

        return trim((string) $titles->item(0)?->textContent) ?: null;
    }

    private function extractMeta(\DOMDocument $dom, string $name): ?string
    {
        $metas = $dom->getElementsByTagName('meta');

        foreach ($metas as $meta) {
            if (strtolower((string) $meta->getAttribute('name')) === strtolower($name)) {
                return trim((string) $meta->getAttribute('content')) ?: null;
            }
        }

        return null;
    }

    private function extractNodes(\DOMDocument $dom): array
    {
        $xpath = new \DOMXPath($dom);
        $query = '//h1|//h2|//h3|//p|//article|//section|//figcaption|//a|//img';

        $nodes = [];

        foreach ($xpath->query($query) ?: [] as $index => $node) {
            if (! $node instanceof \DOMElement) {
                continue;
            }

            $attributes = [];

            foreach ($node->attributes ?? [] as $attribute) {
                $attributes[$attribute->name] = $attribute->value;
            }

            $nodes[] = [
                'tag' => $node->tagName,
                'text' => trim((string) $node->textContent),
                'html' => $dom->saveHTML($node) ?: null,
                'selector' => $this->buildSimpleSelector($node, $index),
                'inside_main' => $this->isInsideMain($node),
                'inside_layout' => $this->isInsideLayout($node),
                'attributes' => $attributes,
                'source_location' => null,
            ];
        }

        return $nodes;
    }

    private function toUrlPath(string $relativePath): string
    {
        if ($relativePath === 'index.html') {
            return '/';
        }

        return '/' . trim(str_replace('.html', '', $relativePath), '/');
    }

    private function buildSimpleSelector(\DOMElement $node, int $index): string
    {
        if ($node->hasAttribute('id')) {
            return '#' . $node->getAttribute('id');
        }

        if ($node->hasAttribute('class')) {
            $firstClass = collect(explode(' ', (string) $node->getAttribute('class')))->filter()->first();

            if ($firstClass) {
                return strtolower($node->tagName) . '.' . $firstClass;
            }
        }

        return strtolower($node->tagName) . ':nth-of-type(' . ($index + 1) . ')';
    }

    private function isInsideMain(\DOMElement $element): bool
    {
        $current = $element;

        while ($current instanceof \DOMElement) {
            if (strtolower($current->tagName) === 'main' || strtolower((string) $current->getAttribute('role')) === 'main') {
                return true;
            }

            $current = $current->parentNode instanceof \DOMElement ? $current->parentNode : null;
        }

        return false;
    }

    private function isInsideLayout(\DOMElement $element): bool
    {
        $current = $element;

        while ($current instanceof \DOMElement) {
            if (in_array(strtolower($current->tagName), ['nav', 'header', 'footer'], true)) {
                return true;
            }

            $current = $current->parentNode instanceof \DOMElement ? $current->parentNode : null;
        }

        return false;
    }
}
