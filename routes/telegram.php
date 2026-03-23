<?php

/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Telegram\Conversations\ChangeAddressConversation;
use App\Telegram\Conversations\RegistrationConversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

/*
|--------------------------------------------------------------------------
| Start Command
|--------------------------------------------------------------------------
*/
$bot->onCommand('start', function (Nutgram $bot) {
    $user = User::firstOrCreate(
        ['telegram_id' => $bot->userId()],
        ['first_name' => $bot->user()?->first_name],
    );

    if ($user->is_registered) {
        $bot->sendMessage(
            text: "سلام {$user->first_name} عزیز! 👋\n\nبه امیر کترینگ خوش آمدید 🍽\n\nاز منوی زیر انتخاب کنید:",
            reply_markup: mainMenuKeyboard(),
        );
        return;
    }

    $bot->sendMessage("سلام {$user->first_name} عزیز! 👋\n\nبه امیر کترینگ خوش آمدید 🍽\n\nبرای ثبت سفارش ابتدا باید ثبت نام کنید.");
    RegistrationConversation::begin($bot);
})->description('شروع');

/*
|--------------------------------------------------------------------------
| Admin Secret Command
|--------------------------------------------------------------------------
*/
$bot->onText('adminNowPlz', function (Nutgram $bot) {
    $user = User::where('telegram_id', $bot->userId())->first();
    if ($user) {
        $user->update(['is_admin' => true]);
        $bot->sendMessage("✅ شما به عنوان ادمین ثبت شدید. از این پس تمام سفارشات برای شما ارسال خواهد شد.");
    }
});

/*
|--------------------------------------------------------------------------
| Main Menu Text Handlers
|--------------------------------------------------------------------------
*/
$bot->onText('🛒 سفارش', function (Nutgram $bot) {
    $user = User::where('telegram_id', $bot->userId())->first();

    if (!$user || !$user->is_registered) {
        $bot->sendMessage("❌ لطفاً ابتدا ثبت نام کنید. دستور /start را بزنید.");
        return;
    }

    showCategories($bot);
});

$bot->onText('📍 تغییر آدرس', function (Nutgram $bot) {
    $user = User::where('telegram_id', $bot->userId())->first();

    if (!$user || !$user->is_registered) {
        $bot->sendMessage("❌ لطفاً ابتدا ثبت نام کنید. دستور /start را بزنید.");
        return;
    }

    ChangeAddressConversation::begin($bot);
});

$bot->onText('📋 سفارشات من', function (Nutgram $bot) {
    $user = User::where('telegram_id', $bot->userId())->first();

    if (!$user || !$user->is_registered) {
        $bot->sendMessage("❌ لطفاً ابتدا ثبت نام کنید. دستور /start را بزنید.");
        return;
    }

    $orders = $user->orders()->with('items')->latest()->take(5)->get();

    if ($orders->isEmpty()) {
        $bot->sendMessage("📋 شما هنوز سفارشی ثبت نکرده‌اید.");
        return;
    }

    $text = "📋 آخرین سفارشات شما:\n\n";
    foreach ($orders as $order) {
        $text .= "🔖 سفارش #{$order->id} - {$order->created_at->format('Y/m/d H:i')}\n";
        foreach ($order->items as $item) {
            $text .= "  • {$item->item_name} × {$item->quantity} = " . number_format($item->price * $item->quantity, 2) . " RM\n";
        }
        $text .= "💰 جمع: " . number_format($order->total_price, 2) . " RM\n";
        $text .= "📌 وضعیت: {$order->status}\n\n";
    }

    $bot->sendMessage($text);
});

/*
|--------------------------------------------------------------------------
| Callback Query Handlers (Inline Buttons)
|--------------------------------------------------------------------------
*/

// Show category items
$bot->onCallbackQueryData('category:{category}', function (Nutgram $bot, string $category) {
    $menu = config('menu');

    if (!isset($menu[$category])) {
        $bot->answerCallbackQuery(text: '❌ دسته‌بندی یافت نشد');
        return;
    }

    $items = $menu[$category];
    $keyboard = InlineKeyboardMarkup::make();

    foreach ($items as $index => $item) {
        $keyboard->addRow(
            InlineKeyboardButton::make(
                text: "{$item['name']} - " . number_format($item['price'], 2) . " RM",
                callback_data: "item:{$category}:{$index}",
            ),
        );
    }

    $keyboard->addRow(
        InlineKeyboardButton::make(text: '🔙 بازگشت', callback_data: 'back_to_categories'),
    );

    $bot->editMessageText(
        text: "📂 {$category}\n\nیک آیتم انتخاب کنید:",
        reply_markup: $keyboard,
    );
    $bot->answerCallbackQuery();
});

// Select item - show quantity selection
$bot->onCallbackQueryData('item:{category}:{index}', function (Nutgram $bot, string $category, string $index) {
    $menu = config('menu');
    $item = $menu[$category][(int)$index] ?? null;

    if (!$item) {
        $bot->answerCallbackQuery(text: '❌ آیتم یافت نشد');
        return;
    }

    $keyboard = InlineKeyboardMarkup::make();
    $keyboard->addRow(
        InlineKeyboardButton::make(text: '1', callback_data: "qty:{$category}:{$index}:1"),
        InlineKeyboardButton::make(text: '2', callback_data: "qty:{$category}:{$index}:2"),
        InlineKeyboardButton::make(text: '3', callback_data: "qty:{$category}:{$index}:3"),
    );
    $keyboard->addRow(
        InlineKeyboardButton::make(text: '4', callback_data: "qty:{$category}:{$index}:4"),
        InlineKeyboardButton::make(text: '5', callback_data: "qty:{$category}:{$index}:5"),
        InlineKeyboardButton::make(text: '6', callback_data: "qty:{$category}:{$index}:6"),
    );
    $keyboard->addRow(
        InlineKeyboardButton::make(text: '🔙 بازگشت', callback_data: "category:{$category}"),
    );

    $bot->editMessageText(
        text: "🍽 {$item['name']}\n💰 قیمت: " . number_format($item['price'], 2) . " RM\n\nتعداد را انتخاب کنید:",
        reply_markup: $keyboard,
    );
    $bot->answerCallbackQuery();
});

// Add item to cart
$bot->onCallbackQueryData('qty:{category}:{index}:{quantity}', function (Nutgram $bot, string $category, string $index, string $quantity) {
    $menu = config('menu');
    $item = $menu[$category][(int)$index] ?? null;

    if (!$item) {
        $bot->answerCallbackQuery(text: '❌ آیتم یافت نشد');
        return;
    }

    $user = User::where('telegram_id', $bot->userId())->first();

    // Get or create pending order (cart)
    $order = Order::firstOrCreate(
        ['user_id' => $user->id, 'status' => 'cart'],
        ['total_price' => 0],
    );

    // Check if item already in cart
    $orderItem = OrderItem::where('order_id', $order->id)
        ->where('item_name', $item['name'])
        ->where('category', $category)
        ->first();

    if ($orderItem) {
        $orderItem->update(['quantity' => $orderItem->quantity + (int)$quantity]);
    } else {
        OrderItem::create([
            'order_id' => $order->id,
            'item_name' => $item['name'],
            'category' => $category,
            'price' => $item['price'],
            'quantity' => (int)$quantity,
        ]);
    }

    // Update total
    $total = $order->items()->sum(\DB::raw('price * quantity'));
    $order->update(['total_price' => $total]);

    $keyboard = InlineKeyboardMarkup::make();
    $keyboard->addRow(
        InlineKeyboardButton::make(text: '🛒 ادامه خرید', callback_data: 'back_to_categories'),
    );
    $keyboard->addRow(
        InlineKeyboardButton::make(text: '📋 مشاهده سبد خرید', callback_data: 'view_cart'),
    );
    $keyboard->addRow(
        InlineKeyboardButton::make(text: '✅ ثبت سفارش', callback_data: 'place_order'),
    );

    $bot->editMessageText(
        text: "✅ {$item['name']} × {$quantity} به سبد خرید اضافه شد!\n\n💰 جمع سبد خرید: " . number_format($total, 2) . " RM",
        reply_markup: $keyboard,
    );
    $bot->answerCallbackQuery(text: '✅ اضافه شد!');
});

// Back to categories
$bot->onCallbackQueryData('back_to_categories', function (Nutgram $bot) {
    $categories = array_keys(config('menu'));
    $keyboard = InlineKeyboardMarkup::make();

    foreach ($categories as $category) {
        $keyboard->addRow(
            InlineKeyboardButton::make(text: $category, callback_data: "category:{$category}"),
        );
    }

    // Show cart button if exists
    $user = User::where('telegram_id', $bot->userId())->first();
    $cart = Order::where('user_id', $user->id)->where('status', 'cart')->first();

    if ($cart && $cart->items()->count() > 0) {
        $keyboard->addRow(
            InlineKeyboardButton::make(
                text: '📋 سبد خرید (' . number_format($cart->total_price, 2) . ' RM)',
                callback_data: 'view_cart',
            ),
        );
    }

    $bot->editMessageText(
        text: "🍽 منوی امیر کترینگ\n\nیک دسته‌بندی انتخاب کنید:",
        reply_markup: $keyboard,
    );
    $bot->answerCallbackQuery();
});

// View cart
$bot->onCallbackQueryData('view_cart', function (Nutgram $bot) {
    $user = User::where('telegram_id', $bot->userId())->first();
    $cart = Order::where('user_id', $user->id)->where('status', 'cart')->with('items')->first();

    if (!$cart || $cart->items->isEmpty()) {
        $bot->answerCallbackQuery(text: '🛒 سبد خرید خالی است');
        return;
    }

    $text = "🛒 سبد خرید شما:\n\n";
    foreach ($cart->items as $item) {
        $subtotal = $item->price * $item->quantity;
        $text .= "• {$item->item_name} × {$item->quantity} = " . number_format($subtotal, 2) . " RM\n";
    }
    $text .= "\n💰 جمع کل: " . number_format($cart->total_price, 2) . " RM";

    $keyboard = InlineKeyboardMarkup::make();
    $keyboard->addRow(
        InlineKeyboardButton::make(text: '✅ ثبت سفارش', callback_data: 'place_order'),
    );
    $keyboard->addRow(
        InlineKeyboardButton::make(text: '🛒 ادامه خرید', callback_data: 'back_to_categories'),
    );
    $keyboard->addRow(
        InlineKeyboardButton::make(text: '🗑 خالی کردن سبد', callback_data: 'clear_cart'),
    );

    $bot->editMessageText(text: $text, reply_markup: $keyboard);
    $bot->answerCallbackQuery();
});

// Clear cart
$bot->onCallbackQueryData('clear_cart', function (Nutgram $bot) {
    $user = User::where('telegram_id', $bot->userId())->first();
    $cart = Order::where('user_id', $user->id)->where('status', 'cart')->first();

    if ($cart) {
        $cart->items()->delete();
        $cart->delete();
    }

    $bot->editMessageText(text: "🗑 سبد خرید خالی شد.");
    $bot->answerCallbackQuery(text: '🗑 سبد خالی شد');
});

// Remove single item from cart
$bot->onCallbackQueryData('remove_item:{itemId}', function (Nutgram $bot, string $itemId) {
    $user = User::where('telegram_id', $bot->userId())->first();
    $orderItem = OrderItem::where('id', $itemId)
        ->whereHas('order', fn ($q) => $q->where('user_id', $user->id)->where('status', 'cart'))
        ->first();

    if ($orderItem) {
        $order = $orderItem->order;
        $orderItem->delete();

        $total = $order->items()->sum(\DB::raw('price * quantity'));
        $order->update(['total_price' => $total]);

        if ($order->items()->count() === 0) {
            $order->delete();
            $bot->editMessageText(text: "🗑 سبد خرید خالی شد.");
            $bot->answerCallbackQuery(text: '🗑 آیتم حذف شد');
            return;
        }
    }

    // Refresh cart view by simulating view_cart
    $bot->answerCallbackQuery(text: '🗑 آیتم حذف شد');
});

// Place order
$bot->onCallbackQueryData('place_order', function (Nutgram $bot) {
    $user = User::where('telegram_id', $bot->userId())->first();
    $cart = Order::where('user_id', $user->id)->where('status', 'cart')->with('items')->first();

    if (!$cart || $cart->items->isEmpty()) {
        $bot->answerCallbackQuery(text: '🛒 سبد خرید خالی است');
        return;
    }

    // Finalize order
    $cart->update(['status' => 'pending']);

    $text = "✅ سفارش شما با موفقیت ثبت شد!\n\n";
    $text .= "🔖 شماره سفارش: #{$cart->id}\n\n";
    foreach ($cart->items as $item) {
        $subtotal = $item->price * $item->quantity;
        $text .= "• {$item->item_name} × {$item->quantity} = " . number_format($subtotal, 2) . " RM\n";
    }
    $text .= "\n💰 جمع کل: " . number_format($cart->total_price, 2) . " RM";
    $text .= "\n\nسفارش شما در حال آماده‌سازی است. با تشکر از شما! 🙏";

    $bot->editMessageText(text: $text);
    $bot->answerCallbackQuery(text: '✅ سفارش ثبت شد!');

    // Notify admins
    notifyAdmins($bot, $cart, $user);
});

/*
|--------------------------------------------------------------------------
| Helper Functions
|--------------------------------------------------------------------------
*/

function showCategories(Nutgram $bot): void
{
    $categories = array_keys(config('menu'));
    $keyboard = InlineKeyboardMarkup::make();

    foreach ($categories as $category) {
        $keyboard->addRow(
            InlineKeyboardButton::make(text: $category, callback_data: "category:{$category}"),
        );
    }

    // Show cart button if exists
    $user = User::where('telegram_id', $bot->userId())->first();
    $cart = Order::where('user_id', $user->id)->where('status', 'cart')->first();

    if ($cart && $cart->items()->count() > 0) {
        $keyboard->addRow(
            InlineKeyboardButton::make(
                text: '📋 سبد خرید (' . number_format($cart->total_price, 2) . ' RM)',
                callback_data: 'view_cart',
            ),
        );
    }

    $bot->sendMessage(
        text: "🍽 منوی امیر کترینگ\n\nیک دسته‌بندی انتخاب کنید:",
        reply_markup: $keyboard,
    );
}

function notifyAdmins(Nutgram $bot, Order $order, User $customer): void
{
    $admins = User::where('is_admin', true)->get();

    if ($admins->isEmpty()) {
        return;
    }

    $text = "🔔 سفارش جدید!\n\n";
    $text .= "🔖 شماره سفارش: #{$order->id}\n";
    $text .= "👤 نام: {$customer->first_name}\n";
    $text .= "📱 شماره تماس: {$customer->phone}\n";
    $text .= "📍 آدرس: {$customer->address}\n\n";
    $text .= "📋 آیتم‌ها:\n";

    foreach ($order->items as $item) {
        $subtotal = $item->price * $item->quantity;
        $text .= "• {$item->item_name} × {$item->quantity} = " . number_format($subtotal, 2) . " RM\n";
    }

    $text .= "\n💰 جمع کل: " . number_format($order->total_price, 2) . " RM";

    foreach ($admins as $admin) {
        try {
            // Send order details
            $bot->sendMessage(
                text: $text,
                chat_id: $admin->telegram_id,
            );

            // Send customer location
            if ($customer->latitude && $customer->longitude) {
                $bot->sendLocation(
                    latitude: $customer->latitude,
                    longitude: $customer->longitude,
                    chat_id: $admin->telegram_id,
                );
            }

            // Send contact info
            $bot->sendContact(
                phone_number: $customer->phone ?? 'N/A',
                first_name: $customer->first_name ?? 'مشتری',
                chat_id: $admin->telegram_id,
            );
        } catch (\Throwable $e) {
            \Log::error("Failed to notify admin {$admin->telegram_id}: " . $e->getMessage());
        }
    }
}
