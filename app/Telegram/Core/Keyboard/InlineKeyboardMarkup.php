<?php

namespace App\Telegram\Core\Keyboard;

class InlineKeyboardMarkup
{
    private array $rows = [];

    public static function make(): static
    {
        return new static();
    }

    public function addRow(InlineKeyboardButton ...$buttons): static
    {
        $this->rows[] = array_map(fn($b) => $b->toArray(), $buttons);
        return $this;
    }

    public function toArray(): array
    {
        return [
            'inline_keyboard' => $this->rows,
        ];
    }
}
