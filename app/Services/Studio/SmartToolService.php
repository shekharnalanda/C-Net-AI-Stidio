<?php

namespace App\Services\Studio;

use Illuminate\Support\Str;

class SmartToolService
{
    public function build(string $tool, string $text, array $options = []): array
    {
        $language = $options['language'] ?? 'en';
        $sentences = collect(preg_split('/(?<=[.!?।])\s+/u', trim($text)))
            ->filter()->values();

        return match ($tool) {
            'script' => $this->script($sentences, $language),
            'scenes' => $this->scenes($sentences),
            'captions' => $this->captions($sentences),
            'prompt' => $this->prompt($text, $options),
            default => throw new \InvalidArgumentException('Unsupported smart tool.'),
        };
    }

    private function script($sentences, string $language): array
    {
        return [
            'format' => 'studio-script-v1',
            'language' => $language,
            'hook' => Str::limit((string) $sentences->first(), 180),
            'body' => $sentences->slice(1)->values()->all(),
            'call_to_action' => $language === 'hi' ? 'अधिक जानकारी के लिए आज ही संपर्क करें।' : 'Contact us today to learn more.',
        ];
    }

    private function scenes($sentences): array
    {
        return ['format' => 'studio-scenes-v1', 'scenes' => $sentences->take(24)->map(fn ($line, $i) => [
            'number' => $i + 1,
            'duration_seconds' => 5,
            'narration' => $line,
            'visual_prompt' => 'Professional cinematic visual: '.Str::limit($line, 300),
            'transition' => $i === 0 ? 'fade-in' : 'cross-dissolve',
        ])->all()];
    }

    private function captions($sentences): array
    {
        $cursor = 0;
        return ['format' => 'studio-captions-v1', 'captions' => $sentences->take(60)->map(function ($line, $i) use (&$cursor) {
            $duration = max(2, min(8, (int) ceil(str_word_count($line) / 2.5)));
            $caption = ['index' => $i + 1, 'start' => $cursor, 'end' => $cursor + $duration, 'text' => $line];
            $cursor += $duration;
            return $caption;
        })->all()];
    }

    private function prompt(string $text, array $options): array
    {
        return ['format' => 'studio-prompt-v1', 'prompt' => trim(sprintf(
            '%s. Style: %s. Aspect ratio: %s. Quality: %s. Use coherent composition, natural lighting and brand-safe visuals.',
            Str::limit($text, 3000, ''), $options['style'] ?? 'professional', $options['aspect_ratio'] ?? '16:9', $options['quality'] ?? 'hd'
        ))];
    }
}
