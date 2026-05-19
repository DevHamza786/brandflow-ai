<?php

declare(strict_types=1);

namespace App\Domains\AI\Services;

use App\Domains\AI\Contracts\PromptRendererContract;
use App\Domains\AI\Contracts\PromptTemplateRegistryContract;
use App\Domains\AI\Data\PromptTemplateDefinition;
use App\Domains\AI\Exceptions\PromptTemplateNotFoundException;
use App\Domains\AI\Exceptions\PromptVariableMissingException;
use Illuminate\Support\Facades\File;

/**
 * Filesystem-backed prompt registry (DB-backed versions added later).
 *
 * Slug format: domain.name → resources/prompts/domain/name/v1.blade.php
 */
final class PromptTemplateRegistry implements PromptTemplateRegistryContract
{
    public function __construct(
        private readonly PromptRendererContract $renderer,
    ) {
    }

    public function resolve(string $slug, ?string $version = null): PromptTemplateDefinition
    {
        $version ??= (string) config('ai.prompts.default_version', 'v1');
        $view = $this->viewName($slug, $version);
        $path = $this->filesystemPath($slug, $version);

        if (! File::exists($path)) {
            throw new PromptTemplateNotFoundException(
                "Prompt template [{$slug}@{$version}] not found at [{$path}].",
                ['slug' => $slug, 'version' => $version]
            );
        }

        return new PromptTemplateDefinition(
            slug: $slug,
            version: $version,
            view: $view,
            requiredVariables: $this->parseRequiredVariables($path),
        );
    }

    /**
     * @param  array<string, mixed>  $variables
     */
    public function render(string $slug, array $variables, ?string $version = null): string
    {
        $definition = $this->resolve($slug, $version);

        $this->assertRequiredVariables($definition, $variables);

        return $this->renderer->render($definition->view, $variables);
    }

    /**
     * @param  array<string, mixed>  $variables
     */
    private function assertRequiredVariables(PromptTemplateDefinition $definition, array $variables): void
    {
        foreach ($definition->requiredVariables as $required) {
            if (! array_key_exists($required, $variables)) {
                throw new PromptVariableMissingException(
                    "Missing required prompt variable [{$required}] for [{$definition->slug}].",
                    ['slug' => $definition->slug, 'variable' => $required]
                );
            }
        }
    }

    private function viewName(string $slug, string $version): string
    {
        $relative = $this->relativePath($slug, $version);

        $namespace = config('ai.prompts.view_namespace', 'prompts');

        return $namespace.'::'.str_replace('/', '.', $relative);
    }

    private function filesystemPath(string $slug, string $version): string
    {
        $base = rtrim((string) config('ai.prompts.base_path'), DIRECTORY_SEPARATOR);

        return $base.DIRECTORY_SEPARATOR.$this->relativePath($slug, $version).'.blade.php';
    }

    private function relativePath(string $slug, string $version): string
    {
        $parts = explode('.', $slug);
        $name = array_pop($parts);
        $domain = implode('/', $parts);

        return $domain.'/'.$name.'/'.$version;
    }

    /**
     * @return list<string>
     */
    private function parseRequiredVariables(string $path): array
    {
        $content = File::get($path);

        if (! preg_match('/variables:\s*(.+)/', $content, $matches)) {
            return [];
        }

        preg_match_all('/\$(\w+)/', $matches[1], $vars);

        return array_values(array_unique($vars[1] ?? []));
    }
}
