<?php

namespace App\Telegram\Core;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramClient
{
    private string $baseUrl;

    public function __construct(private readonly string $token)
    {
        $this->baseUrl = "https://api.telegram.org/bot{$token}/";
    }

    /**
     * Call a Telegram Bot API method.
     *
     * @throws \RuntimeException on API error
     */
    public function call(string $method, array $params = []): array
    {
        $channel = config('telegram.log_channel', 'null');

        try {
            $response = Http::post($this->baseUrl . $method, $params);
            $body = $response->json();

            if (!($body['ok'] ?? false)) {
                $error = $body['description'] ?? 'Unknown Telegram API error';
                Log::channel($channel)->error("Telegram API error [{$method}]: {$error}", $params);
                throw new \RuntimeException("Telegram API error: {$error}");
            }

            return $body['result'] ?? [];
        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::channel($channel)->error("Telegram HTTP error [{$method}]: " . $e->getMessage());
            throw new \RuntimeException("Telegram HTTP error: " . $e->getMessage(), 0, $e);
        }
    }
}
