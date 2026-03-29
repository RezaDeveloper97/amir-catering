<?php

namespace App\Providers;

use App\Telegram\Core\ConversationManager;
use App\Telegram\Core\TelegramBot;
use App\Telegram\Core\TelegramClient;
use Illuminate\Support\ServiceProvider;

class TelegramServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            base_path('config/telegram.php'), 'telegram'
        );

        $this->app->singleton(TelegramClient::class, fn () =>
            new TelegramClient((string) config('telegram.token', ''))
        );

        $this->app->singleton(ConversationManager::class);

        $this->app->singleton(TelegramBot::class, fn ($app) =>
            new TelegramBot(
                $app->make(TelegramClient::class),
                $app->make(ConversationManager::class),
            )
        );
    }

    public function boot(): void
    {
        $this->publishes([
            base_path('config/telegram.php') => config_path('telegram.php'),
        ], 'telegram-config');

        // Load routes/telegram.php — the $bot variable is the TelegramBot singleton
        $bot = $this->app->make(TelegramBot::class);
        require base_path('routes/telegram.php');
    }
}
