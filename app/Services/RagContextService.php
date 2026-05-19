<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class RagContextService
{
    /**
     * @return array<int, string>
     */
    public function retrieveRelevantFragments(string $question, int $limit = 3): array
    {
        $kbPath = config('services.minimaika.kb_path', storage_path('app/private/minimaika_kb.txt'));

        if (! File::exists($kbPath)) {
            return [];
        }

        $text = trim((string) File::get($kbPath));
        if ($text === '') {
            return [];
        }

        $chunks = preg_split('/\n\s*\n+/u', $text) ?: [];
        $keywords = $this->tokenize($question);

        $scored = [];
        foreach ($chunks as $chunk) {
            $cleanChunk = $this->truncateWords(trim($chunk), 250);
            if ($cleanChunk === '') {
                continue;
            }

            $score = 0;
            $chunkLower = mb_strtolower($cleanChunk);
            foreach ($keywords as $keyword) {
                if ($keyword !== '' && str_contains($chunkLower, $keyword)) {
                    $score++;
                }
            }

            if ($score > 0) {
                $scored[] = ['score' => $score, 'chunk' => $cleanChunk];
            }
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        $selected = array_slice(array_column($scored, 'chunk'), 0, max(1, $limit));

        return $this->fitWordBudget($selected, 800);
    }

    /** @return array<int, string> */
    private function tokenize(string $text): array
    {
        $normalized = mb_strtolower($text);
        $tokens = preg_split('/[^\p{L}\p{N}]+/u', $normalized) ?: [];

        return array_values(array_filter($tokens, fn (string $token) => mb_strlen($token) >= 3));
    }

    /** @param array<int, string> $fragments */
    private function fitWordBudget(array $fragments, int $maxWords): array
    {
        $result = [];
        $wordsUsed = 0;

        foreach ($fragments as $fragment) {
            $count = str_word_count($fragment);
            if ($wordsUsed + $count <= $maxWords) {
                $result[] = $fragment;
                $wordsUsed += $count;
                continue;
            }

            $remaining = $maxWords - $wordsUsed;
            if ($remaining <= 0) {
                break;
            }

            $result[] = $this->truncateWords($fragment, $remaining);
            break;
        }

        return array_slice($result, 0, 3);
    }

    private function truncateWords(string $text, int $maxWords): string
    {
        $words = preg_split('/\s+/u', trim($text)) ?: [];
        if (count($words) <= $maxWords) {
            return trim($text);
        }

        return trim(implode(' ', array_slice($words, 0, $maxWords)));
    }
}
