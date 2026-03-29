<?php

namespace App\Telegram\Core\Keyboard;

class InlineKeyboardButton
{
    public function __construct(
        public readonly string $text,
        public readonly ?string $callback_data = null,
        public readonly ?string $url = null,
    ) {}

    public static function make(string $text, ?string $callback_data = null, ?string $url = null): static
    {
        return new static(text: $text, callback_data: $callback_data, url: $url);
    }

    public function toArray(): array
    {
        $data = ['text' => $this->text];

        if ($this->callback_data !== null) {
            $data['callback_data'] = $this->callback_data;
        }

        if ($this->url !== null) {
            $data['url'] = $this->url;
        }

        return $data;
    }
}
