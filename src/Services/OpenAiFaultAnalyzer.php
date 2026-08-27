<?php

declare(strict_types=1);

namespace HeritageApps\ErrorMonitor\Services;

use HeritageApps\ErrorMonitor\Contracts\ErrorAnalyzer;
use HeritageApps\ErrorMonitor\Models\SystemError;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Default AI analyzer - a direct OpenAI chat-completion call using
 * config('error-monitor.openai.*'). Bound unless the host app registers its
 * own ErrorAnalyzer (e.g. to route the call through a cost-tracked client).
 */
class OpenAiFaultAnalyzer implements ErrorAnalyzer
{
    private const MAX_FRAMES = 3;

    private const SNIPPET_CONTEXT_LINES = 10;

    /**
     * Ask the LLM for a short root-cause writeup from the trace and surrounding
     * source code only. Never pass request/session/user data - the prompt is
     * built exclusively from exception_class, message, file/line and code text.
     */
    public function analyze(SystemError $error): ?string
    {
        if (! config('error-monitor.openai.key')) {
            return null;
        }

        $prompt = $this->buildPrompt($error);

        try {
            $response = Http::timeout((int) config('error-monitor.openai.timeout', 30))
                ->withHeaders([
                    'Authorization' => 'Bearer '.config('error-monitor.openai.key'),
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => config('error-monitor.openai.model', 'gpt-4o-mini'),
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'max_tokens' => 800,
                ]);
        } catch (Throwable $e) {
            Log::error('OpenAiFaultAnalyzer: request failed', ['error' => $e->getMessage()]);

            return null;
        }

        if (! $response->successful()) {
            Log::error('OpenAiFaultAnalyzer: API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        return $response->json('choices.0.message.content');
    }

    private function buildPrompt(SystemError $error): string
    {
        $frames = $this->appFrames($error);

        $snippets = collect($frames)
            ->map(fn (array $frame) => $this->renderSnippet($frame['file'], $frame['line']))
            ->filter()
            ->implode("\n\n");

        return <<<PROMPT
        You are diagnosing an unhandled exception in a Laravel application. Based only on the
        information below, give a short (3-6 sentence) likely root cause, followed by a brief
        suggested fix if one is apparent. Do not ask questions, just give your best analysis.

        Exception: {$error->exception_class}
        Message: {$error->message}

        Relevant source:
        {$snippets}

        Stack trace:
        {$error->trace}
        PROMPT;
    }

    /**
     * @return array<int, array{file: string, line: int}>
     */
    private function appFrames(SystemError $error): array
    {
        $frames = [];

        if ($error->file) {
            $frames[] = ['file' => $error->file, 'line' => (int) $error->line];
        }

        foreach (explode("\n", (string) $error->trace) as $line) {
            if (count($frames) >= self::MAX_FRAMES) {
                break;
            }

            if (! preg_match('/^#\d+\s+(.+?)\((\d+)\):/', $line, $matches)) {
                continue;
            }

            [, $file, $lineNumber] = $matches;

            if (str_contains($file, '/vendor/')) {
                continue;
            }

            $frames[] = ['file' => $file, 'line' => (int) $lineNumber];
        }

        return $frames;
    }

    private function renderSnippet(string $file, int $line): ?string
    {
        if (! is_file($file)) {
            return null;
        }

        $lines = file($file);

        if ($lines === false) {
            return null;
        }

        $start = max(0, $line - 1 - self::SNIPPET_CONTEXT_LINES);
        $end = min(count($lines), $line + self::SNIPPET_CONTEXT_LINES);

        $snippet = collect(array_slice($lines, $start, $end - $start))
            ->map(fn (string $text, int $i) => ($start + $i + 1).': '.rtrim($text))
            ->implode("\n");

        return "{$file}:{$line}\n{$snippet}";
    }
}
