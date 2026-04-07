<?php

namespace App\Http\Controllers;

use App\Jobs\PullSiteRepository;
use App\Models\Site;
use App\Models\WebhookEvent;
use App\Services\RepoUrlNormalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GithubWebhookController extends Controller
{
    public function __construct(private readonly RepoUrlNormalizer $repoUrlNormalizer)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $signature = (string) $request->header('X-Hub-Signature-256');
        $deliveryId = (string) $request->header('X-GitHub-Delivery');
        $event = (string) $request->header('X-GitHub-Event', 'unknown');
        $payload = $request->getContent();

        abort_unless($this->hasValidSignature($signature, $payload), Response::HTTP_UNAUTHORIZED);

        $decoded = $request->json()->all();
        $repoUrl = $this->repoUrlNormalizer->normalize((string) data_get($decoded, 'repository.html_url', ''));

        WebhookEvent::create([
            'provider' => 'github',
            'event' => $event,
            'delivery_id' => $deliveryId,
            'repository' => data_get($decoded, 'repository.full_name'),
            'payload' => $decoded,
            'processed_at' => now(),
        ]);

        $site = Site::where('repo_url', $repoUrl)->first();

        if ($site && $event === 'push') {
            PullSiteRepository::dispatch($site->id);
        }

        return response()->json(['ok' => true]);
    }

    private function hasValidSignature(string $signature, string $payload): bool
    {
        $secret = (string) config('pixelkraft.github.webhook_secret');

        if ($secret === '' || $signature === '') {
            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
    }
}
