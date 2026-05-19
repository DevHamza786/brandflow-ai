<?php

declare(strict_types=1);

namespace App\Domains\AI\Data;

use App\Domains\AI\Enums\AiMessageRole;
use App\Domains\Shared\Data\DataTransferObject;

final class AiMessage extends DataTransferObject
{
    public function __construct(
        public readonly AiMessageRole $role,
        public readonly string $content,
    ) {
    }

    /**
     * @return array{role: string, content: string}
     */
    public function toProviderArray(): array
    {
        return [
            'role' => $this->role->value,
            'content' => $this->content,
        ];
    }

    /**
     * @param  array{role: string, content: string}  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            role: AiMessageRole::from($payload['role']),
            content: $payload['content'],
        );
    }
}
