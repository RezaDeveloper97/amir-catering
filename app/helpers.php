<?php

use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

if (!function_exists('mainMenuKeyboard')) {
    function mainMenuKeyboard(): ReplyKeyboardMarkup
    {
        return ReplyKeyboardMarkup::make(resize_keyboard: true, is_persistent: true)
            ->addRow(
                KeyboardButton::make('🛒 سفارش'),
                KeyboardButton::make('📍 تغییر آدرس'),
            )
            ->addRow(
                KeyboardButton::make('📋 سفارشات من'),
            );
    }
}
