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
        $bot->sendMessage(text: "📍 لطفاً آدرس جدید خود را وارد کنید:");
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
            text: "📍 حالا لطفاً لوکیشن جدید خود را ارسال کنید:",
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

        $bot->sendMessage(
            text: "✅ آدرس شما با موفقیت تغییر کرد!",
            reply_markup: mainMenuKeyboard($user),
        );
        $this->end();
    }
}
