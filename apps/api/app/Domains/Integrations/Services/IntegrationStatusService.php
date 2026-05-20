<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Services;

use App\Domains\Integrations\Contracts\LinkedInIntegrationRepositoryContract;
use App\Domains\Integrations\Data\LinkedInIntegrationDto;
use App\Domains\Integrations\Enums\IntegrationStatus;

final class IntegrationStatusService
{
    public function __construct(
        private readonly LinkedInIntegrationRepositoryContract $integrations,
    ) {
    }

    public function markConnected(string $workspaceId, string $integrationId): LinkedInIntegrationDto
    {
        return $this->integrations->updateStatus(
            $workspaceId,
            $integrationId,
            IntegrationStatus::Connected,
        );
    }

    public function markExpired(string $workspaceId, string $integrationId, string $reason): LinkedInIntegrationDto
    {
        return $this->integrations->updateStatus(
            $workspaceId,
            $integrationId,
            IntegrationStatus::Expired,
            $reason,
        );
    }

    public function markError(string $workspaceId, string $integrationId, string $error): LinkedInIntegrationDto
    {
        return $this->integrations->updateStatus(
            $workspaceId,
            $integrationId,
            IntegrationStatus::Error,
            $error,
        );
    }

    public function markDisconnected(string $workspaceId, string $integrationId): LinkedInIntegrationDto
    {
        return $this->integrations->updateStatus(
            $workspaceId,
            $integrationId,
            IntegrationStatus::Disconnected,
        );
    }
}
