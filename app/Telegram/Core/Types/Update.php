<?php

namespace App\Telegram\Core\Types;

class Update
{
    public function __construct(
        public readonly int $update_id,
        public readonly ?Message $message = null,
        public readonly ?CallbackQuery $callback_query = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            update_id: (int) $data['update_id'],
            message: isset($data['message']) ? Message::fromArray($data['message']) : null,
            callback_query: isset($data['callback_query']) ? CallbackQuery::fromArray($data['callback_query']) : null,
        );
    }
}
