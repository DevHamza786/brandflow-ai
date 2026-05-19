<?php

declare(strict_types=1);

namespace App\Domains\AI\Providers;

use App\Domains\AI\Adapters\GeminiAdapter;
use App\Domains\AI\Adapters\NullLlmGateway;
use App\Domains\AI\Adapters\OpenAiAdapter;
use App\Domains\AI\Contracts\LlmGateway;
use App\Domains\AI\Contracts\LlmProviderFactoryContract;
use App\Domains\AI\Contracts\PromptRendererContract;
use App\Domains\AI\Contracts\PromptTemplateRegistryContract;
use App\Domains\AI\Factories\LlmProviderFactory;
use App\Domains\AI\Services\LlmGatewayService;
use App\Domains\AI\Services\PromptRenderer;
use App\Domains\AI\Services\PromptTemplateRegistry;
use App\Domains\AI\Support\MemoryPromptAssembler;
use App\Domains\AI\Support\ProviderHttpClient;
use App\Domains\AI\Support\RetryExecutor;
use App\Domains\AI\Support\StructuredOutputDecoder;
use App\Domains\Shared\Providers\DomainServiceProvider;
use Illuminate\Support\Facades\View;

final class AIServiceProvider extends DomainServiceProvider
{
    protected function domainName(): string
    {
        return 'AI';
    }

    public function boot(): void
    {
        $basePath = config('ai.prompts.base_path', resource_path('prompts'));
        $namespace = config('ai.prompts.view_namespace', 'prompts');

        View::addNamespace($namespace, $basePath);
    }

    protected function registerServices(): void
    {
        $this->app->singleton(ProviderHttpClient::class);
        $this->app->singleton(StructuredOutputDecoder::class);
        $this->app->singleton(RetryExecutor::class);
        $this->app->singleton(MemoryPromptAssembler::class);

        $this->app->singleton(OpenAiAdapter::class);
        $this->app->singleton(GeminiAdapter::class);

        $this->app->singleton(LlmProviderFactoryContract::class, LlmProviderFactory::class);

        $this->app->singleton(PromptRendererContract::class, PromptRenderer::class);
        $this->app->singleton(PromptTemplateRegistryContract::class, PromptTemplateRegistry::class);

        $this->registerGateway();
    }

    private function registerGateway(): void
    {
        if (config('ai.use_null_gateway', false)) {
            $this->app->singleton(LlmGateway::class, NullLlmGateway::class);

            return;
        }

        $this->app->singleton(LlmGateway::class, LlmGatewayService::class);
    }
}
