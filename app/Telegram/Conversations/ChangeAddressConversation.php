<?php

namespace App\Telegram\Conversations;

use App\Models\User;
use App\Telegram\Core\Conversation;
use App\Telegram\Core\Keyboard\KeyboardButton;
use App\Telegram\Core\Keyboard\ReplyKeyboardMarkup;

class ChangeAddressConversation extends Conversation
{
    /** Back-to-menu button texts across all languages */
    private const BACK_BUTTONS = ['🔴 بازگشت به منو', '🔴 Back to Menu', '🔴 Kembali ke Menu'];

    private function isBackButton(?string $text): bool
    {
        return $text !== null && in_array($text, self::BACK_BUTTONS);
    }

    private function handleBack(User $user): void
    {
        $this->end();
        $this->bot->sendMessage(
            text: trans_user('choose_from_menu', $user),
            reply_markup: mainMenuKeyboard($user),
        );
    }

    public function start(): void
    {
        $user = User::where('telegram_id', $this->bot->userId())->first();
        $this->bot->sendMessage(text: trans_user('addr_enter_new', $user));
        $this->next('handleAddress');
    }

    public function handleAddress(): void
    {
        $address = $this->bot->message()?->text;
        $user    = User::where('telegram_id', $this->bot->userId())->first();

        if ($this->isBackButton($address)) {
            $this->handleBack($user);
            return;
        }

        if (empty($address)) {
            $this->bot->sendMessage(trans_user('addr_text_only', $user));
            $this->next('handleAddress');
            return;
        }

        $user?->update(['address' => $address]);

        $keyboard = ReplyKeyboardMarkup::make(resize_keyboard: true, one_time_keyboard: true)
            ->addRow(KeyboardButton::make(trans_user('btn_send_location', $user), request_location: true));

        $this->bot->sendMessage(
            text: trans_user('addr_send_location', $user),
            reply_markup: $keyboard,
        );
        $this->next('handleLocation');
    }

    public function handleLocation(): void
    {
        $user = User::where('telegram_id', $this->bot->userId())->first();

        if ($this->isBackButton($this->bot->message()?->text)) {
            $this->handleBack($user);
            return;
        }

        $location = $this->bot->message()?->location;

        if ($location === null) {
            $keyboard = ReplyKeyboardMarkup::make(resize_keyboard: true, one_time_keyboard: true)
                ->addRow(KeyboardButton::make(trans_user('btn_send_location', $user), request_location: true));

            $this->bot->sendMessage(
                text: trans_user('addr_use_location_button', $user),
                reply_markup: $keyboard,
            );
            $this->next('handleLocation');
            return;
        }

        $user?->update([
            'latitude'  => $location->latitude,
            'longitude' => $location->longitude,
        ]);

        $this->bot->sendMessage(
            text: trans_user('addr_success', $user),
            reply_markup: mainMenuKeyboard($user),
        );
        $this->end();
    }
}
