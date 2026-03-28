<?php

namespace App\Telegram\Conversations;

use App\Models\User;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

class ChangeAddressConversation extends Conversation
{
    public function start(Nutgram $bot): void
    {
        $user = User::where('telegram_id', $bot->userId())->first();
        $bot->sendMessage(text: trans_user('addr_enter_new', $user));
        $this->next('handleAddress');
    }

    public function handleAddress(Nutgram $bot): void
    {
        $address = $bot->message()?->text;
        $user = User::where('telegram_id', $bot->userId())->first();

        if (empty($address)) {
            $bot->sendMessage(trans_user('addr_text_only', $user));
            $this->next('handleAddress');
            return;
        }

        $user?->update(['address' => $address]);

        $keyboard = ReplyKeyboardMarkup::make(resize_keyboard: true, one_time_keyboard: true)
            ->addRow(KeyboardButton::make(trans_user('btn_send_location', $user), request_location: true));

        $bot->sendMessage(
            text: trans_user('addr_send_location', $user),
            reply_markup: $keyboard,
        );
        $this->next('handleLocation');
    }

    public function handleLocation(Nutgram $bot): void
    {
        $location = $bot->message()?->location;
        $user = User::where('telegram_id', $bot->userId())->first();

        if ($location === null) {
            $keyboard = ReplyKeyboardMarkup::make(resize_keyboard: true, one_time_keyboard: true)
                ->addRow(KeyboardButton::make(trans_user('btn_send_location', $user), request_location: true));

            $bot->sendMessage(
                text: trans_user('addr_use_location_button', $user),
                reply_markup: $keyboard,
            );
            $this->next('handleLocation');
            return;
        }

        $user?->update([
            'latitude' => $location->latitude,
            'longitude' => $location->longitude,
        ]);

        $bot->sendMessage(
            text: trans_user('addr_success', $user),
            reply_markup: mainMenuKeyboard($user),
        );
        $this->end();
    }
}
