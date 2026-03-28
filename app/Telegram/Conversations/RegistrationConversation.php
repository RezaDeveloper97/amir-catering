<?php

namespace App\Telegram\Conversations;

use App\Models\User;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardRemove;

class RegistrationConversation extends Conversation
{
    public function start(Nutgram $bot): void
    {
        $user = User::where('telegram_id', $bot->userId())->first();

        $bot->sendMessage(
            text: trans_user('reg_enter_address', $user),
            reply_markup: ReplyKeyboardRemove::make(remove_keyboard: true),
        );
        $this->next('handleAddress');
    }

    public function handleAddress(Nutgram $bot): void
    {
        $address = $bot->message()?->text;
        $user = User::where('telegram_id', $bot->userId())->first();

        if (empty($address)) {
            $bot->sendMessage(trans_user('reg_address_text_only', $user));
            $this->next('handleAddress');
            return;
        }

        $user?->update(['address' => $address]);

        $keyboard = ReplyKeyboardMarkup::make(resize_keyboard: true, one_time_keyboard: true)
            ->addRow(KeyboardButton::make(trans_user('btn_send_location', $user), request_location: true));

        $bot->sendMessage(
            text: trans_user('reg_send_location', $user),
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
                text: trans_user('reg_use_location_button', $user),
                reply_markup: $keyboard,
            );
            $this->next('handleLocation');
            return;
        }

        $user?->update([
            'latitude' => $location->latitude,
            'longitude' => $location->longitude,
        ]);

        $keyboard = ReplyKeyboardMarkup::make(resize_keyboard: true, one_time_keyboard: true)
            ->addRow(KeyboardButton::make(trans_user('btn_send_phone', $user), request_contact: true));

        $bot->sendMessage(
            text: trans_user('reg_send_phone', $user),
            reply_markup: $keyboard,
        );
        $this->next('handlePhone');
    }

    public function handlePhone(Nutgram $bot): void
    {
        $contact = $bot->message()?->contact;
        $user = User::where('telegram_id', $bot->userId())->first();

        if ($contact === null) {
            $keyboard = ReplyKeyboardMarkup::make(resize_keyboard: true, one_time_keyboard: true)
                ->addRow(KeyboardButton::make(trans_user('btn_send_phone', $user), request_contact: true));

            $bot->sendMessage(
                text: trans_user('reg_use_phone_button', $user),
                reply_markup: $keyboard,
            );
            $this->next('handlePhone');
            return;
        }

        $user?->update([
            'phone' => $contact->phone_number,
            'is_registered' => true,
        ]);

        $bot->sendMessage(
            text: trans_user('reg_complete', $user),
            reply_markup: mainMenuKeyboard($user),
        );
        $this->end();
    }
}
