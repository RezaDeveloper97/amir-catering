<?php

use App\Models\User;
use App\Telegram\Core\Keyboard\KeyboardButton;
use App\Telegram\Core\Keyboard\ReplyKeyboardMarkup;

if (!function_exists('trans_user')) {
    function trans_user(string $key, ?User $user = null, array $replace = []): string
    {
        $locale = $user->language ?? 'fa';
        $file = lang_path("{$locale}/bot.php");

        if (!file_exists($file)) {
            $file = lang_path('fa/bot.php');
        }

        static $cache = [];
        if (!isset($cache[$locale])) {
            $cache[$locale] = require $file;
        }

        $text = $cache[$locale][$key] ?? $key;

        foreach ($replace as $placeholder => $value) {
            $text = str_replace(":{$placeholder}", $value, $text);
        }

        return $text;
    }
}

if (!function_exists('backToMenuKeyboard')) {
    function backToMenuKeyboard(?User $user = null): ReplyKeyboardMarkup
    {
        return ReplyKeyboardMarkup::make(resize_keyboard: true, is_persistent: true)
            ->addRow(KeyboardButton::make(trans_user('btn_back_to_menu', $user)));
    }
}

if (!function_exists('adminMenuKeyboard')) {
    function adminMenuKeyboard(?User $user = null): ReplyKeyboardMarkup
    {
        return ReplyKeyboardMarkup::make(resize_keyboard: true, is_persistent: true)
            ->addRow(KeyboardButton::make(trans_user('btn_manage_categories', $user)))
            ->addRow(KeyboardButton::make(trans_user('btn_manage_items', $user)))
            ->addRow(KeyboardButton::make(trans_user('btn_back_to_menu', $user)));
    }
}

if (!function_exists('mainMenuKeyboard')) {
    function mainMenuKeyboard(?User $user = null): ReplyKeyboardMarkup
    {
        $keyboard = ReplyKeyboardMarkup::make(resize_keyboard: true, is_persistent: true)
            ->addRow(
                KeyboardButton::make(trans_user('btn_order', $user)),
                KeyboardButton::make(trans_user('btn_change_address', $user)),
            )
            ->addRow(
                KeyboardButton::make(trans_user('btn_my_orders', $user)),
                KeyboardButton::make(trans_user('btn_language', $user)),
            );

        if ($user?->is_admin) {
            $keyboard->addRow(
                KeyboardButton::make(trans_user('btn_admin_panel', $user)),
            );
        }

        return $keyboard;
    }
}
