<?php

namespace App\Telegram\Core;

use App\Telegram\Core\Keyboard\InlineKeyboardMarkup;
use App\Telegram\Core\Keyboard\ReplyKeyboardMarkup;
use App\Telegram\Core\Keyboard\ReplyKeyboardRemove;
use App\Telegram\Core\Types\CallbackQuery;
use App\Telegram\Core\Types\Message;
use App\Telegram\Core\Types\TelegramUser;
use App\Telegram\Core\Types\Update;

class TelegramBot
{
    // --- Routing tables ---
    private array $middlewares     = [];
    private array $commands        = [];
    private array $callbackHandlers = [];
    private array $textHandlers    = [];
    private mixed $messageHandler = null;

    // --- Current update state (set during processUpdate) ---
    private ?Update $currentUpdate = null;
    private array $store           = []; // $bot->get() / $bot->set()

    public function __construct(
        private readonly TelegramClient $client,
        private readonly ConversationManager $conversations,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Routing Registration (used in routes/telegram.php at boot time)
    |--------------------------------------------------------------------------
    */

    public function middleware(callable $fn): void
    {
        $this->middlewares[] = $fn;
    }

    public function onCommand(string $command, callable $handler): self
    {
        $this->commands[ltrim($command, '/')] = $handler;
        return $this;
    }

    /** No-op: maintains API compatibility with Nutgram's command description chaining. */
    public function description(string $description): self
    {
        return $this;
    }

    /**
     * Register a callback query handler.
     * Pattern supports {param} placeholders, e.g. "category:{id}"
     */
    public function onCallbackQueryData(string $pattern, callable $handler): void
    {
        $this->callbackHandlers[] = ['pattern' => $pattern, 'handler' => $handler];
    }

    /**
     * Register a text handler (exact string or regex wrapped in slashes).
     * Pattern without slashes is treated as a literal match.
     * Pattern starting/ending with ( is treated as a regex (Nutgram compat).
     */
    public function onText(string $pattern, callable $handler): void
    {
        $this->textHandlers[] = ['pattern' => $pattern, 'handler' => $handler];
    }

    public function onMessage(callable $handler): void
    {
        $this->messageHandler = $handler;
    }

    /*
    |--------------------------------------------------------------------------
    | Dispatch (called from webhook controller)
    |--------------------------------------------------------------------------
    */

    public function processUpdate(array $data): void
    {
        $this->currentUpdate = Update::fromArray($data);
        $this->store         = [];

        // Build and run middleware chain
        $core = function () {
            $this->dispatchCore();
        };

        $chain = array_reduce(
            array_reverse($this->middlewares),
            fn($next, $middleware) => fn() => $middleware($this, $next),
            $core
        );

        $chain();
    }

    private function dispatchCore(): void
    {
        $update = $this->currentUpdate;

        // 1. Try to resume an active conversation (only for message updates)
        if ($update->message && ($userId = $this->userId())) {
            if ($this->conversations->resume($userId, $this)) {
                return;
            }
        }

        // 2. Command handlers (e.g. /start)
        if ($update->message && $update->message->text) {
            $text = $update->message->text;

            if (str_starts_with($text, '/')) {
                $command = strtolower(substr(explode(' ', $text)[0], 1));
                // Strip @botname suffix if present
                $command = explode('@', $command)[0];

                if (isset($this->commands[$command])) {
                    ($this->commands[$command])($this);
                    return;
                }
            }
        }

        // 3. Callback query handlers
        if ($update->callback_query) {
            $data = $update->callback_query->data ?? '';

            foreach ($this->callbackHandlers as ['pattern' => $pattern, 'handler' => $handler]) {
                $params = $this->matchCallbackPattern($pattern, $data);
                if ($params !== null) {
                    $handler($this, ...$params);
                    return;
                }
            }
        }

        // 4. Text pattern handlers
        if ($update->message && $update->message->text) {
            $text = $update->message->text;

            foreach ($this->textHandlers as ['pattern' => $pattern, 'handler' => $handler]) {
                if ($this->matchText($pattern, $text)) {
                    $handler($this);
                    return;
                }
            }
        }

        // 5. Catch-all message handler
        if ($update->message && $this->messageHandler) {
            ($this->messageHandler)($this);
        }
    }

    /**
     * Match a callback query data pattern like "category:{id}" or "qty:{itemId}:{quantity}".
     * Returns an ordered array of captured values, or null if no match.
     */
    private function matchCallbackPattern(string $pattern, string $data): ?array
    {
        // Split on {param} placeholders, keeping the param names as tokens
        $parts = preg_split('/\{(\w+)\}/', $pattern, -1, PREG_SPLIT_DELIM_CAPTURE);

        $regex = '';
        for ($i = 0; $i < count($parts); $i++) {
            if ($i % 2 === 0) {
                // Literal text segment — escape for regex
                $regex .= preg_quote($parts[$i], '/');
            } else {
                // Param name — becomes a named capture group
                $regex .= '(?P<' . $parts[$i] . '>[^:]+)';
            }
        }
        $regex = '/^' . $regex . '$/';

        if (!preg_match($regex, $data, $matches)) {
            return null;
        }

        // Return only the named capture values (no numeric keys)
        return array_values(array_filter(
            $matches,
            fn($k) => is_string($k),
            ARRAY_FILTER_USE_KEY
        ));
    }

    /**
     * Match text against a pattern.
     * Patterns starting with ( are treated as regex (Nutgram-style alternation groups).
     * All other patterns are exact string matches.
     */
    private function matchText(string $pattern, string $text): bool
    {
        // Regex pattern: starts with ( like "(🛒 سفارش|🛒 Order|🛒 Pesanan)"
        if (str_starts_with($pattern, '(')) {
            return (bool) preg_match('/^' . $pattern . '$/', $text);
        }

        return $text === $pattern;
    }

    /*
    |--------------------------------------------------------------------------
    | Context Methods (used inside handler closures)
    |--------------------------------------------------------------------------
    */

    public function sendMessage(
        string $text,
        ?int $chat_id = null,
        string $parse_mode = 'HTML',
        mixed $reply_markup = null,
    ): void {
        $params = [
            'chat_id'    => $chat_id ?? $this->chatId(),
            'text'       => $text,
            'parse_mode' => $parse_mode,
        ];

        if ($reply_markup !== null) {
            $params['reply_markup'] = $this->serializeMarkup($reply_markup);
        }

        try {
            $this->client->call('sendMessage', $params);
        } catch (\Throwable) {
            // Silently fail — errors already logged in TelegramClient
        }
    }

    public function editMessageText(
        string $text,
        ?int $chat_id = null,
        ?int $message_id = null,
        mixed $reply_markup = null,
        string $parse_mode = 'HTML',
    ): void {
        $resolvedChatId    = $chat_id ?? $this->callbackChatId();
        $resolvedMessageId = $message_id ?? $this->callbackMessageId();

        if (!$resolvedChatId || !$resolvedMessageId) {
            return;
        }

        $params = [
            'chat_id'    => $resolvedChatId,
            'message_id' => $resolvedMessageId,
            'text'       => $text,
            'parse_mode' => $parse_mode,
        ];

        if ($reply_markup !== null) {
            $params['reply_markup'] = $this->serializeMarkup($reply_markup);
        }

        try {
            $this->client->call('editMessageText', $params);
        } catch (\Throwable) {}
    }

    public function answerCallbackQuery(?string $text = null, bool $show_alert = false): void
    {
        $cq = $this->currentUpdate?->callback_query;
        if (!$cq) {
            return;
        }

        $params = ['callback_query_id' => $cq->id];

        if ($text !== null) {
            $params['text']       = $text;
            $params['show_alert'] = $show_alert;
        }

        try {
            $this->client->call('answerCallbackQuery', $params);
        } catch (\Throwable) {}
    }

    public function sendLocation(
        float $latitude,
        float $longitude,
        ?int $chat_id = null,
    ): void {
        try {
            $this->client->call('sendLocation', [
                'chat_id'   => $chat_id ?? $this->chatId(),
                'latitude'  => $latitude,
                'longitude' => $longitude,
            ]);
        } catch (\Throwable) {}
    }

    public function sendContact(
        string $phone_number,
        string $first_name,
        ?int $chat_id = null,
    ): void {
        try {
            $this->client->call('sendContact', [
                'chat_id'      => $chat_id ?? $this->chatId(),
                'phone_number' => $phone_number,
                'first_name'   => $first_name,
            ]);
        } catch (\Throwable) {}
    }

    /*
    |--------------------------------------------------------------------------
    | Update Accessors
    |--------------------------------------------------------------------------
    */

    public function userId(): ?int
    {
        $from = $this->currentUpdate?->message?->from
            ?? $this->currentUpdate?->callback_query?->from;

        return $from?->id;
    }

    public function chatId(): ?int
    {
        return $this->currentUpdate?->message?->chat_id
            ?? $this->currentUpdate?->callback_query?->message?->chat_id;
    }

    public function message(): ?Message
    {
        return $this->currentUpdate?->message;
    }

    public function callbackQuery(): ?CallbackQuery
    {
        return $this->currentUpdate?->callback_query;
    }

    /** The Telegram user object from the current update */
    public function user(): ?TelegramUser
    {
        return $this->currentUpdate?->message?->from
            ?? $this->currentUpdate?->callback_query?->from;
    }

    /*
    |--------------------------------------------------------------------------
    | Shared Store (get/set for passing data between middleware and handlers)
    |--------------------------------------------------------------------------
    */

    public function get(string $key): mixed
    {
        return $this->store[$key] ?? null;
    }

    public function set(string $key, mixed $value): void
    {
        $this->store[$key] = $value;
    }

    /*
    |--------------------------------------------------------------------------
    | Conversation
    |--------------------------------------------------------------------------
    */

    public function beginConversation(Conversation $conversation): void
    {
        $this->conversations->begin($conversation, $this);
    }

    /*
    |--------------------------------------------------------------------------
    | Internals
    |--------------------------------------------------------------------------
    */

    private function callbackChatId(): ?int
    {
        return $this->currentUpdate?->callback_query?->message?->chat_id;
    }

    private function callbackMessageId(): ?int
    {
        return $this->currentUpdate?->callback_query?->message?->message_id;
    }

    private function serializeMarkup(mixed $markup): string
    {
        if (is_string($markup)) {
            return $markup;
        }

        if ($markup instanceof InlineKeyboardMarkup
            || $markup instanceof ReplyKeyboardMarkup
            || $markup instanceof ReplyKeyboardRemove) {
            return json_encode($markup->toArray());
        }

        if (is_array($markup)) {
            return json_encode($markup);
        }

        return json_encode($markup);
    }
}
