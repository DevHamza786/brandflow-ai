<?php

declare(strict_types=1);

namespace App\Domains\Agents\Providers;

use App\Domains\Agents\Agents\HookAgent\HookAgent;
use App\Domains\Agents\Contracts\AgentContract;
use App\Domains\Agents\Contracts\AgentRunRepositoryContract;
use App\Domains\Agents\Repositories\AgentRunRepository;
use App\Domains\Agents\Services\AgentRunner;
use App\Domains\Shared\Providers\DomainServiceProvider;
use InvalidArgumentException;

final class AgentsServiceProvider extends DomainServiceProvider
{
    protected function domainName(): string
    {
        return 'Agents';
    }

    protected function registerRepositories(): void
    {
        $this->app->bind(AgentRunRepositoryContract::class, AgentRunRepository::class);
    }

    protected function registerServices(): void
    {
        $this->app->singleton(AgentRunner::class);
        $this->registerAgentBindings();
    }

    private function registerAgentBindings(): void
    {
        /** @var array<string, array{class?: class-string<AgentContract>|null}> $agents */
        $agents = config('agents.agents', []);

        foreach ($agents as $slug => $definition) {
            $class = $definition['class'] ?? null;

            if ($class === null) {
                continue;
            }

            if (! is_subclass_of($class, AgentContract::class)) {
                throw new InvalidArgumentException("Agent [{$slug}] must implement AgentContract.");
            }

            $this->app->bind("agent.{$slug}", $class);
        }
    }
}
