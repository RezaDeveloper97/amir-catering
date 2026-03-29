<?php

namespace App\Telegram\Core\Keyboard;

class ReplyKeyboardRemove
{
    public function __construct(
        public readonly bool $remove_keyboard = true,
    ) {}

    public static function make(bool $remove_keyboard = true): static
    {
        return new static(remove_keyboard: $remove_keyboard);
    }

    public function toArray(): array
    {
        return [
            'remove_keyboard' => $this->remove_keyboard,
        ];
    }
}
