<?php

namespace App\Telegram\Core\Types;

class TelegramUser
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $first_name = null,
        public readonly ?string $last_name = null,
        public readonly ?string $username = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id: (int) $data['id'],
            first_name: $data['first_name'] ?? null,
            last_name: $data['last_name'] ?? null,
            username: $data['username'] ?? null,
        );
    }
}
