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
        $bot->sendMessage(
            text: "📍 لطفاً آدرس خود را وارد کنید:",
            reply_markup: ReplyKeyboardRemove::make(remove_keyboard: true),
        );
        $this->next('handleAddress');
    }

    public function handleAddress(Nutgram $bot): void
    {
        $address = $bot->message()?->text;

        if (empty($address)) {
            $bot->sendMessage("❌ لطفاً آدرس خود را به صورت متنی وارد کنید:");
            $this->next('handleAddress');
            return;
        }

        $user = User::where('telegram_id', $bot->userId())->first();
        $user?->update(['address' => $address]);

        $keyboard = ReplyKeyboardMarkup::make(resize_keyboard: true, one_time_keyboard: true)
            ->addRow(KeyboardButton::make('📍 ارسال لوکیشن', request_location: true));

        $bot->sendMessage(
            text: "📍 حالا لطفاً لوکیشن خود را ارسال کنید:",
            reply_markup: $keyboard,
        );
        $this->next('handleLocation');
    }

    public function handleLocation(Nutgram $bot): void
    {
        $location = $bot->message()?->location;

        if ($location === null) {
            $keyboard = ReplyKeyboardMarkup::make(resize_keyboard: true, one_time_keyboard: true)
                ->addRow(KeyboardButton::make('📍 ارسال لوکیشن', request_location: true));

            $bot->sendMessage(
                text: "❌ لطفاً از دکمه زیر برای ارسال لوکیشن استفاده کنید:",
                reply_markup: $keyboard,
            );
            $this->next('handleLocation');
            return;
        }

        $user = User::where('telegram_id', $bot->userId())->first();
        $user?->update([
            'latitude' => $location->latitude,
            'longitude' => $location->longitude,
        ]);

        $keyboard = ReplyKeyboardMarkup::make(resize_keyboard: true, one_time_keyboard: true)
            ->addRow(KeyboardButton::make('📱 ارسال شماره موبایل', request_contact: true));

        $bot->sendMessage(
            text: "📱 لطفاً شماره موبایل خود را ارسال کنید:",
            reply_markup: $keyboard,
        );
        $this->next('handlePhone');
    }

    public function handlePhone(Nutgram $bot): void
    {
        $contact = $bot->message()?->contact;

        if ($contact === null) {
            $keyboard = ReplyKeyboardMarkup::make(resize_keyboard: true, one_time_keyboard: true)
                ->addRow(KeyboardButton::make('📱 ارسال شماره موبایل', request_contact: true));

            $bot->sendMessage(
                text: "❌ لطفاً از دکمه زیر برای ارسال شماره موبایل استفاده کنید:",
                reply_markup: $keyboard,
            );
            $this->next('handlePhone');
            return;
        }

        $user = User::where('telegram_id', $bot->userId())->first();
        $user?->update([
            'phone' => $contact->phone_number,
            'is_registered' => true,
        ]);

        $bot->sendMessage(
            text: "✅ ثبت نام شما با موفقیت تکمیل شد!\n\nبه امیر کترینگ خوش آمدید 🎉",
            reply_markup: mainMenuKeyboard(),
        );
        $this->end();
    }
}
