<?php

namespace App\Telegram\Core\Types;

class Message
{
    public function __construct(
        public readonly int $message_id,
        public readonly int $chat_id,
        public readonly ?string $text = null,
        public readonly ?Contact $contact = null,
        public readonly ?Location $location = null,
        public readonly ?TelegramUser $from = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            message_id: (int) $data['message_id'],
            chat_id: (int) ($data['chat']['id'] ?? 0),
            text: $data['text'] ?? null,
            contact: isset($data['contact']) ? Contact::fromArray($data['contact']) : null,
            location: isset($data['location']) ? Location::fromArray($data['location']) : null,
            from: isset($data['from']) ? TelegramUser::fromArray($data['from']) : null,
        );
    }
}
