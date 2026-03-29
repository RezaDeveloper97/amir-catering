<?php

namespace App\Telegram\Core\Keyboard;

class ReplyKeyboardMarkup
{
    private array $rows = [];

    public function __construct(
        public readonly bool $resize_keyboard = false,
        public readonly bool $is_persistent = false,
        public readonly bool $one_time_keyboard = false,
    ) {}

    public static function make(
        bool $resize_keyboard = false,
        bool $is_persistent = false,
        bool $one_time_keyboard = false,
    ): static {
        return new static(
            resize_keyboard: $resize_keyboard,
            is_persistent: $is_persistent,
            one_time_keyboard: $one_time_keyboard,
        );
    }

    public function addRow(KeyboardButton ...$buttons): static
    {
        $this->rows[] = array_map(fn($b) => $b->toArray(), $buttons);
        return $this;
    }

    public function toArray(): array
    {
        $data = [
            'keyboard' => $this->rows,
        ];

        if ($this->resize_keyboard) {
            $data['resize_keyboard'] = true;
        }

        if ($this->is_persistent) {
            $data['is_persistent'] = true;
        }

        if ($this->one_time_keyboard) {
            $data['one_time_keyboard'] = true;
        }

        return $data;
    }
}
