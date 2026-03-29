<?php

namespace App\Telegram\Core\Keyboard;

class KeyboardButton
{
    public function __construct(
        public readonly string $text,
        public readonly bool $request_location = false,
        public readonly bool $request_contact = false,
    ) {}

    public static function make(
        string $text,
        bool $request_location = false,
        bool $request_contact = false,
    ): static {
        return new static(
            text: $text,
            request_location: $request_location,
            request_contact: $request_contact,
        );
    }

    public function toArray(): array
    {
        $data = ['text' => $this->text];

        if ($this->request_location) {
            $data['request_location'] = true;
        }

        if ($this->request_contact) {
            $data['request_contact'] = true;
        }

        return $data;
    }
}
