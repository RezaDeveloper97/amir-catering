<?php

namespace App\Telegram\Conversations;

use App\Models\User;
use App\Telegram\Core\Conversation;
use App\Telegram\Core\Keyboard\KeyboardButton;
use App\Telegram\Core\Keyboard\ReplyKeyboardMarkup;
use App\Telegram\Core\Keyboard\ReplyKeyboardRemove;

class RegistrationConversation extends Conversation
{
    public function start(): void
    {
        $user = User::where('telegram_id', $this->bot->userId())->first();

        $this->bot->sendMessage(
            text: trans_user('reg_enter_address', $user),
            reply_markup: ReplyKeyboardRemove::make(remove_keyboard: true),
        );
        $this->next('handleAddress');
    }

    public function handleAddress(): void
    {
        $address = $this->bot->message()?->text;
        $user    = User::where('telegram_id', $this->bot->userId())->first();

        if (empty($address)) {
            $this->bot->sendMessage(trans_user('reg_address_text_only', $user));
            $this->next('handleAddress');
            return;
        }

        $user?->update(['address' => $address]);

        $keyboard = ReplyKeyboardMarkup::make(resize_keyboard: true, one_time_keyboard: true)
            ->addRow(KeyboardButton::make(trans_user('btn_send_location', $user), request_location: true));

        $this->bot->sendMessage(
            text: trans_user('reg_send_location', $user),
            reply_markup: $keyboard,
        );
        $this->next('handleLocation');
    }

    public function handleLocation(): void
    {
        $location = $this->bot->message()?->location;
        $user     = User::where('telegram_id', $this->bot->userId())->first();

        if ($location === null) {
            $keyboard = ReplyKeyboardMarkup::make(resize_keyboard: true, one_time_keyboard: true)
                ->addRow(KeyboardButton::make(trans_user('btn_send_location', $user), request_location: true));

            $this->bot->sendMessage(
                text: trans_user('reg_use_location_button', $user),
                reply_markup: $keyboard,
            );
            $this->next('handleLocation');
            return;
        }

        $user?->update([
            'latitude'  => $location->latitude,
            'longitude' => $location->longitude,
        ]);

        $keyboard = ReplyKeyboardMarkup::make(resize_keyboard: true, one_time_keyboard: true)
            ->addRow(KeyboardButton::make(trans_user('btn_send_phone', $user), request_contact: true));

        $this->bot->sendMessage(
            text: trans_user('reg_send_phone', $user),
            reply_markup: $keyboard,
        );
        $this->next('handlePhone');
    }

    public function handlePhone(): void
    {
        $contact = $this->bot->message()?->contact;
        $user    = User::where('telegram_id', $this->bot->userId())->first();

        if ($contact === null) {
            $keyboard = ReplyKeyboardMarkup::make(resize_keyboard: true, one_time_keyboard: true)
                ->addRow(KeyboardButton::make(trans_user('btn_send_phone', $user), request_contact: true));

            $this->bot->sendMessage(
                text: trans_user('reg_use_phone_button', $user),
                reply_markup: $keyboard,
            );
            $this->next('handlePhone');
            return;
        }

        $user?->update([
            'phone'         => $contact->phone_number,
            'is_registered' => true,
        ]);

        $this->bot->sendMessage(
            text: trans_user('reg_complete', $user),
            reply_markup: mainMenuKeyboard($user),
        );
        $this->end();
    }
}
