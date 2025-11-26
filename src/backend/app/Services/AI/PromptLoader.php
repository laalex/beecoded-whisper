<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\File;
use RuntimeException;

class PromptLoader
{
    /**
     * Load a prompt template by name and replace placeholders with context.
     */
    public function load(string $promptName, array $replacements = []): string
    {
        $config = config("prompts.{$promptName}");

        if (!$config) {
            throw new RuntimeException("Prompt '{$promptName}' not found in config.");
        }

        $path = $config['path'];

        if (!File::exists($path)) {
            throw new RuntimeException("Prompt file not found: {$path}");
        }

        $template = File::get($path);

        return $this->replacePlaceholders($template, $replacements);
    }

    /**
     * Get the max tokens configured for a prompt.
     */
    public function getMaxTokens(string $promptName): int
    {
        return config("prompts.{$promptName}.max_tokens", 1024);
    }

    /**
     * Get prompt configuration.
     */
    public function getConfig(string $promptName): ?array
    {
        return config("prompts.{$promptName}");
    }

    /**
     * Replace {{placeholder}} style placeholders with values.
     */
    private function replacePlaceholders(string $template, array $replacements): string
    {
        foreach ($replacements as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $stringValue = is_array($value) ? json_encode($value, JSON_PRETTY_PRINT) : (string) $value;
            $template = str_replace($placeholder, $stringValue, $template);
        }

        return $template;
    }
}
