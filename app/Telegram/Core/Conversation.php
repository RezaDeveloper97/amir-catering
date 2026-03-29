<?php

namespace App\Telegram\Core;

abstract class Conversation
{
    protected TelegramBot $bot;
    private ConversationManager $manager;

    /**
     * Injected by ConversationManager::begin() before start() is called.
     */
    public function _init(TelegramBot $bot, ConversationManager $manager): void
    {
        $this->bot     = $bot;
        $this->manager = $manager;
    }

    /**
     * Static shorthand: RegistrationConversation::begin($bot)
     */
    public static function begin(TelegramBot $bot): void
    {
        $instance = new static();
        $bot->beginConversation($instance);
    }

    /**
     * Entry point — must be implemented by each conversation.
     */
    abstract public function start(): void;

    /**
     * Store the next step to call when the user sends a message.
     */
    protected function next(string $step): void
    {
        $userId = $this->bot->userId();
        if ($userId) {
            $this->manager->set($userId, static::class, $step);
        }
    }

    /**
     * End the conversation and clear state from cache.
     */
    protected function end(): void
    {
        $userId = $this->bot->userId();
        if ($userId) {
            $this->manager->clear($userId);
        }
    }
}
