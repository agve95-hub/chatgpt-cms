<?php

namespace App\Services;

class ProjectDetector
{
    public function detect(string $repoPath): array
    {
        $packageJson = $this->readJson($repoPath . '/package.json');

        if ($this->packageContains($packageJson, 'astro')) {
            return $this->detected('astro', 'npx astro build', 'dist');
        }

        if ($this->packageContains($packageJson, 'next')) {
            return $this->detected('react', 'npm run build', '.next');
        }

        if ($this->packageContains($packageJson, 'nuxt')) {
            return $this->detected('vue', 'npm run build', '.output/public');
        }

        if ($this->packageContains($packageJson, 'svelte')) {
            return $this->detected('svelte', 'npm run build', 'build');
        }

        if ($this->packageContains($packageJson, 'react')) {
            return $this->detected('react', 'npm run build', 'dist');
        }

        if ($this->packageContains($packageJson, 'vue')) {
            return $this->detected('vue', 'npm run build', 'dist');
        }

        if ($this->exists($repoPath, ['hugo.toml', 'config.toml'])) {
            return $this->detected('hugo', 'hugo', 'public');
        }

        if ($this->exists($repoPath, ['.eleventy.js', 'eleventy.config.js'])) {
            return $this->detected('11ty', 'npx @11ty/eleventy', '_site');
        }

        if (count(glob($repoPath . '/*.html') ?: []) > 0) {
            return $this->detected('static_html', null, '.');
        }

        return $this->detected('custom', null, null);
    }

    private function packageContains(?array $packageJson, string $needle): bool
    {
        if (! $packageJson) {
            return false;
        }

        $deps = array_merge(
            $packageJson['dependencies'] ?? [],
            $packageJson['devDependencies'] ?? []
        );

        return collect(array_keys($deps))->contains(
            fn (string $dependency): bool => str_contains(strtolower($dependency), strtolower($needle))
        );
    }

    private function readJson(string $path): ?array
    {
        if (! is_file($path)) {
            return null;
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : null;
    }

    private function exists(string $repoPath, array $candidates): bool
    {
        foreach ($candidates as $candidate) {
            if (is_file($repoPath . '/' . $candidate)) {
                return true;
            }
        }

        return false;
    }

    private function detected(string $projectType, ?string $buildCommand, ?string $outputDir): array
    {
        return [
            'project_type' => $projectType,
            'build_command' => $buildCommand,
            'build_output_dir' => $outputDir,
        ];
    }
}
