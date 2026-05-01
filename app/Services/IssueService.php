<?php

namespace App\Services;

use App\Models\Issue;
use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IssueService
{
    public function create(array $data): Issue
    {
        return Issue::create($this->enrich($data));
    }

    public function update(Issue $issue, array $data): Issue
    {
        // Re-enrich only when content fields change to avoid unnecessary AI calls.
        if (array_key_exists('description', $data) || array_key_exists('priority', $data)) {
            $merged   = array_merge($issue->toArray(), $data);
            $enriched = $this->enrich($merged);
            $data['summary']          = $enriched['summary'];
            $data['suggested_action'] = $enriched['suggested_action'];
            $data['is_escalated']     = $enriched['is_escalated'];
        } else {
            // Even without content changes, re-check escalation (e.g. status change).
            $merged = array_merge($issue->toArray(), $data);
            $data['is_escalated'] = $this->shouldEscalate($merged);
        }

        $issue->update($data);

        return $issue->fresh();
    }

    /**
     * Re-evaluate escalation on an existing issue without changing other fields.
     * Useful for a scheduled command that flags stale issues.
     */
    public function reEvaluateEscalation(Issue $issue): void
    {
        $shouldEscalate = $this->shouldEscalate($issue->toArray());

        if ($issue->is_escalated !== $shouldEscalate) {
            $issue->update(['is_escalated' => $shouldEscalate]);
        }
    }

    // ── Private: enrichment pipeline ─────────────────────────────────────────

    private function enrich(array $data): array
    {
        $result = $this->generateSummary($data['description'] ?? '');

        $data['summary']          = $result['summary'];
        $data['suggested_action'] = $result['suggested_action'];
        $data['is_escalated']     = $this->shouldEscalate($data);

        return $data;
    }

    private function generateSummary(string $description): array
    {
        $result = $this->tryAiSummarize($description) ?? $this->ruleBasedSummary($description);

        // Cap length on every result, regardless of source.
        $result['summary'] = $this->truncate($result['summary']);

        return $result;
    }

    // ── Private: AI summarization ─────────────────────────────────────────────

    /**
     * Attempt to summarize via OpenRouter.
     * Returns null on any failure, triggering ruleBasedSummary() as fallback.
     */
    private function tryAiSummarize(string $description): ?array
    {
        $apiKey = config('services.openrouter.key');

        if (empty($apiKey)) {
            return null;
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(8)
                ->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model'           => config('services.openrouter.model', 'gpt-4o-mini'),
                    'messages'        => [
                        [
                            'role'    => 'system',
                            'content' => 'You are a support assistant. Return JSON with two keys: summary and action. Keep both short and clear.',
                        ],
                        [
                            'role'    => 'user',
                            'content' => $description,
                        ],
                    ],
                    'max_tokens'      => 120,
                    'temperature'     => 0.2,
                    'response_format' => ['type' => 'json_object'],
                ]);

            if ($response->failed()) {
                Log::warning('AI summarization failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            return $this->parseAiResponse($response);

        } catch (\Throwable $e) {
            Log::warning('AI summarization exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Parse and validate the AI response payload.
     * Returns null if the shape is unexpected, so the fallback takes over.
     */
    private function parseAiResponse(Response $response): ?array
    {
        $parsed = json_decode($response->json('choices.0.message.content'), true);

        if (empty($parsed['summary']) || empty($parsed['action'])) {
            Log::warning('AI response missing expected keys', ['parsed' => $parsed]);
            return null;
        }

        return [
            'summary'          => $parsed['summary'],
            'suggested_action' => $parsed['action'],
        ];
    }

    // ── Private: rules-based fallback ────────────────────────────────────────

    private function ruleBasedSummary(string $description): array
    {
        $lower = strtolower($description);

        if (str_contains($lower, 'payment')) {
            return [
                'summary'          => 'Payment-related issue detected',
                'suggested_action' => 'Check transaction logs and payment gateway status',
            ];
        }

        if (str_contains($lower, 'error') || str_contains($lower, 'exception')) {
            return [
                'summary'          => 'System error or exception encountered',
                'suggested_action' => 'Check application logs and reproduce the issue in staging',
            ];
        }

        if (str_contains($lower, 'login') || str_contains($lower, 'auth') || str_contains($lower, 'password')) {
            return [
                'summary'          => 'Authentication or access issue reported',
                'suggested_action' => 'Verify credentials, session config, and auth middleware',
            ];
        }

        if (str_contains($lower, 'slow') || str_contains($lower, 'timeout') || str_contains($lower, 'performance')) {
            return [
                'summary'          => 'Performance or timeout issue reported',
                'suggested_action' => 'Check database queries, cache hits, and server load',
            ];
        }

        return [
            'summary'          => $this->truncate($description) ?: 'Uncategorized issue reported',
            'suggested_action' => 'Assign to the appropriate team and investigate further',
        ];
    }

    // ── Private: utilities ───────────────────────────────────────────────────

    /**
     * Truncate to the nearest word boundary within $limit characters.
     */
    private function truncate(string $text, int $limit = 160): string
    {
        $clean = strip_tags($text);

        if (mb_strlen($clean) <= $limit) {
            return $clean;
        }

        $truncated = mb_substr($clean, 0, $limit);
        $lastSpace = mb_strrpos($truncated, ' ');

        return ($lastSpace !== false ? mb_substr($truncated, 0, $lastSpace) : $truncated) . '...';
    }

    private function shouldEscalate(array $data): bool
    {
        $status = $data['status'] ?? 'open';

        // Resolved/closed issues never need escalation.
        if (in_array($status, ['resolved', 'closed'])) {
            return false;
        }

        // Rule 1: high priority + still active.
        if (($data['priority'] ?? '') === 'high') {
            return true;
        }

        // Rule 2: any priority, open/in-progress for more than 48 hours.
        $createdAt = $data['created_at'] ?? null;

        if ($createdAt === null) {
            return false;
        }

        $createdAt = $createdAt instanceof Carbon ? $createdAt : Carbon::parse($createdAt);
        $age = $createdAt->diffInHours(now());

        return $age >= 48;
    }
}
