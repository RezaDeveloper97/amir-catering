<?php

namespace App\Telegram\Core;

use Illuminate\Support\Facades\Cache;

class ConversationManager
{
    private const TTL = 3600; // 1 hour conversation TTL

    private function key(int $userId): string
    {
        return "tg_conv_{$userId}";
    }

    public function begin(Conversation $conv, TelegramBot $bot): void
    {
        $conv->_init($bot, $this);
        $conv->start();
    }

    /**
     * Resume an active conversation for the given userId.
     * Returns true if a conversation was found and resumed, false otherwise.
     */
    public function resume(int $userId, TelegramBot $bot): bool
    {
        $state = Cache::get($this->key($userId));

        if (!$state || !isset($state['class'], $state['step'])) {
            return false;
        }

        $class = $state['class'];
        $step  = $state['step'];

        if (!class_exists($class) || !method_exists($class, $step)) {
            $this->clear($userId);
            return false;
        }

        /** @var Conversation $conv */
        $conv = new $class();
        $conv->_init($bot, $this);
        $conv->{$step}();

        return true;
    }

    public function set(int $userId, string $class, string $step): void
    {
        Cache::put($this->key($userId), ['class' => $class, 'step' => $step], self::TTL);
    }

    public function clear(int $userId): void
    {
        Cache::forget($this->key($userId));
    }

    public function exists(int $userId): bool
    {
        return Cache::has($this->key($userId));
    }
}
