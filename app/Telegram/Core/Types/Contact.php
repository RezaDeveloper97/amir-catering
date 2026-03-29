<?php

namespace App\Telegram\Core\Types;

class Contact
{
    public function __construct(
        public readonly string $phone_number,
        public readonly string $first_name,
        public readonly ?string $last_name = null,
        public readonly ?int $user_id = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            phone_number: $data['phone_number'],
            first_name: $data['first_name'],
            last_name: $data['last_name'] ?? null,
            user_id: isset($data['user_id']) ? (int) $data['user_id'] : null,
        );
    }
}
