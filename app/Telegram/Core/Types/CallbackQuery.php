<?php

namespace App\Telegram\Core\Types;

class CallbackQuery
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $data = null,
        public readonly ?Message $message = null,
        public readonly ?TelegramUser $from = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'],
            data: $data['data'] ?? null,
            message: isset($data['message']) ? Message::fromArray($data['message']) : null,
            from: isset($data['from']) ? TelegramUser::fromArray($data['from']) : null,
        );
    }
}
