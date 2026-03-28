<?php

/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Models\Category;
use App\Models\MenuItem;
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
| Middleware - Resolve user once per request
|--------------------------------------------------------------------------
*/
$bot->middleware(function (Nutgram $bot, $next) {
    $userId = $bot->userId();
    if ($userId) {
        $user = User::where('telegram_id', $userId)->first();
        $bot->set('user', $user);
    }
    $next($bot);
});

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
    $bot->set('user', $user);

    if ($user->is_registered) {
        $bot->sendMessage(
            text: "سلام {$user->first_name} عزیز! 👋\n\nبه امیر کترینگ خوش آمدید 🍽\n\nاز منوی زیر انتخاب کنید:",
            reply_markup: mainMenuKeyboard($user),
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
    $user = $bot->get('user');
    if ($user) {
        $user->update(['is_admin' => true]);
        $bot->sendMessage(
            text: "✅ شما به عنوان ادمین ثبت شدید. از این پس تمام سفارشات برای شما ارسال خواهد شد.",
            reply_markup: mainMenuKeyboard($user->refresh()),
        );
    }
});

/*
|--------------------------------------------------------------------------
| Admin Menu Command
|--------------------------------------------------------------------------
*/
$bot->onCommand('admin', function (Nutgram $bot) {
    $user = $bot->get('user');
    if (!$user || !$user->is_admin) {
        $bot->sendMessage("❌ شما دسترسی ادمین ندارید.");
        return;
    }
    showAdminPanel($bot);
});

/*
|--------------------------------------------------------------------------
| Main Menu Text Handlers
|--------------------------------------------------------------------------
*/
$bot->onText('🛒 سفارش', function (Nutgram $bot) {
    $user = $bot->get('user');

    if (!$user || !$user->is_registered) {
        $bot->sendMessage("❌ لطفاً ابتدا ثبت نام کنید. دستور /start را بزنید.");
        return;
    }

    $bot->sendMessage(text: '🍽 در حال نمایش منو...', reply_markup: backToMenuKeyboard());
    showCategories($bot);
});

$bot->onText('📍 تغییر آدرس', function (Nutgram $bot) {
    $user = $bot->get('user');

    if (!$user || !$user->is_registered) {
        $bot->sendMessage("❌ لطفاً ابتدا ثبت نام کنید. دستور /start را بزنید.");
        return;
    }

    $bot->sendMessage(text: '📍 در حال تغییر آدرس...', reply_markup: backToMenuKeyboard());
    ChangeAddressConversation::begin($bot);
});

$bot->onText('⚙️ پنل مدیریت', function (Nutgram $bot) {
    $user = $bot->get('user');

    if (!$user || !$user->is_admin) {
        $bot->sendMessage("❌ شما دسترسی ادمین ندارید.");
        return;
    }

    $bot->sendMessage(text: '⚙️ در حال نمایش پنل مدیریت...', reply_markup: backToMenuKeyboard());
    showAdminPanel($bot);
});

$bot->onText('📋 سفارشات من', function (Nutgram $bot) {
    $user = $bot->get('user');

    if (!$user || !$user->is_registered) {
        $bot->sendMessage("❌ لطفاً ابتدا ثبت نام کنید. دستور /start را بزنید.");
        return;
    }

    $orders = $user->orders()->where('status', '!=', 'cart')->with('items')->latest()->limit(5)->get();

    if ($orders->isEmpty()) {
        $bot->sendMessage(
            text: "📋 شما هنوز سفارشی ثبت نکرده‌اید.",
            reply_markup: backToMenuKeyboard(),
        );
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

    $bot->sendMessage(text: $text, reply_markup: backToMenuKeyboard());
});

/*
|--------------------------------------------------------------------------
| Callback Query Handlers - Customer Menu
|--------------------------------------------------------------------------
*/

// Show category items
$bot->onCallbackQueryData('category:{id}', function (Nutgram $bot, string $id) {
    $category = Category::with(['items' => fn($q) => $q->where('is_active', true)])->find($id);

    if (!$category) {
        $bot->answerCallbackQuery(text: '❌ دسته‌بندی یافت نشد');
        return;
    }

    $keyboard = InlineKeyboardMarkup::make();

    foreach ($category->items as $item) {
        $keyboard->addRow(
            InlineKeyboardButton::make(
                text: "🔵 {$item->name} - " . number_format($item->price, 2) . " RM",
                callback_data: "item:{$item->id}",
            ),
        );
    }

    $keyboard->addRow(
        InlineKeyboardButton::make(text: '🔴 بازگشت', callback_data: 'back_to_categories'),
    );

    $bot->editMessageText(
        text: "📂 {$category->name}\n\nیک آیتم انتخاب کنید:",
        reply_markup: $keyboard,
    );
    $bot->answerCallbackQuery();
});

// Select item - show quantity selection
$bot->onCallbackQueryData('item:{id}', function (Nutgram $bot, string $id) {
    $item = MenuItem::with('category')->find($id);

    if (!$item) {
        $bot->answerCallbackQuery(text: '❌ آیتم یافت نشد');
        return;
    }

    $keyboard = InlineKeyboardMarkup::make();
    $keyboard->addRow(
        InlineKeyboardButton::make(text: '1', callback_data: "qty:{$item->id}:1"),
        InlineKeyboardButton::make(text: '2', callback_data: "qty:{$item->id}:2"),
        InlineKeyboardButton::make(text: '3', callback_data: "qty:{$item->id}:3"),
    );
    $keyboard->addRow(
        InlineKeyboardButton::make(text: '4', callback_data: "qty:{$item->id}:4"),
        InlineKeyboardButton::make(text: '5', callback_data: "qty:{$item->id}:5"),
        InlineKeyboardButton::make(text: '6', callback_data: "qty:{$item->id}:6"),
    );
    $keyboard->addRow(
        InlineKeyboardButton::make(text: '🔴 بازگشت', callback_data: "category:{$item->category_id}"),
    );

    $bot->editMessageText(
        text: "🍽 {$item->name}\n💰 قیمت: " . number_format($item->price, 2) . " RM\n\nتعداد را انتخاب کنید:",
        reply_markup: $keyboard,
    );
    $bot->answerCallbackQuery();
});

// Add item to cart
$bot->onCallbackQueryData('qty:{itemId}:{quantity}', function (Nutgram $bot, string $itemId, string $quantity) {
    $item = MenuItem::with('category')->find($itemId);

    if (!$item) {
        $bot->answerCallbackQuery(text: '❌ آیتم یافت نشد');
        return;
    }

    $user = $bot->get('user');

    $order = Order::firstOrCreate(
        ['user_id' => $user->id, 'status' => 'cart'],
        ['total_price' => 0],
    );

    $orderItem = OrderItem::where('order_id', $order->id)
        ->where('item_name', $item->name)
        ->where('category', $item->category->name)
        ->first();

    if ($orderItem) {
        $orderItem->update(['quantity' => $orderItem->quantity + (int)$quantity]);
    } else {
        OrderItem::create([
            'order_id' => $order->id,
            'item_name' => $item->name,
            'category' => $item->category->name,
            'price' => $item->price,
            'quantity' => (int)$quantity,
        ]);
    }

    $total = $order->items()->sum(\DB::raw('price * quantity'));
    $order->update(['total_price' => $total]);

    $keyboard = InlineKeyboardMarkup::make();
    $keyboard->addRow(
        InlineKeyboardButton::make(text: '🔵 ادامه خرید', callback_data: 'back_to_categories'),
    );
    $keyboard->addRow(
        InlineKeyboardButton::make(text: '🔵 مشاهده سبد خرید', callback_data: 'view_cart'),
    );
    $keyboard->addRow(
        InlineKeyboardButton::make(text: '🟢 ثبت سفارش', callback_data: 'place_order'),
    );

    $bot->editMessageText(
        text: "✅ {$item->name} × {$quantity} به سبد خرید اضافه شد!\n\n💰 جمع سبد خرید: " . number_format($total, 2) . " RM",
        reply_markup: $keyboard,
    );
    $bot->answerCallbackQuery(text: '✅ اضافه شد!');
});

// Back to categories
$bot->onCallbackQueryData('back_to_categories', function (Nutgram $bot) {
    $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
    $keyboard = InlineKeyboardMarkup::make();

    foreach ($categories as $category) {
        $keyboard->addRow(
            InlineKeyboardButton::make(text: "🔵 {$category->name}", callback_data: "category:{$category->id}"),
        );
    }

    $user = $bot->get('user');
    $cart = Order::where('user_id', $user->id)->where('status', 'cart')->withCount('items')->first();

    if ($cart && $cart->items_count > 0) {
        $keyboard->addRow(
            InlineKeyboardButton::make(
                text: '🔵 سبد خرید (' . number_format($cart->total_price, 2) . ' RM)',
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
    $user = $bot->get('user');
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
        InlineKeyboardButton::make(text: '🟢 ثبت سفارش', callback_data: 'place_order'),
    );
    $keyboard->addRow(
        InlineKeyboardButton::make(text: '🔵 ادامه خرید', callback_data: 'back_to_categories'),
    );
    $keyboard->addRow(
        InlineKeyboardButton::make(text: '🔴 خالی کردن سبد', callback_data: 'clear_cart'),
    );

    $bot->editMessageText(text: $text, reply_markup: $keyboard);
    $bot->answerCallbackQuery();
});

// Clear cart
$bot->onCallbackQueryData('clear_cart', function (Nutgram $bot) {
    $user = $bot->get('user');
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
    $user = $bot->get('user');
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

    $bot->answerCallbackQuery(text: '🗑 آیتم حذف شد');
});

// Place order
$bot->onCallbackQueryData('place_order', function (Nutgram $bot) {
    $user = $bot->get('user');
    $cart = Order::where('user_id', $user->id)->where('status', 'cart')->with('items')->first();

    if (!$cart || $cart->items->isEmpty()) {
        $bot->answerCallbackQuery(text: '🛒 سبد خرید خالی است');
        return;
    }

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

    notifyAdmins($bot, $cart, $user);
});

/*
|--------------------------------------------------------------------------
| Admin Callback Handlers
|--------------------------------------------------------------------------
*/

// Admin panel
$bot->onCallbackQueryData('admin_panel', function (Nutgram $bot) {
    $user = $bot->get('user');
    if (!$user?->is_admin) return;

    $keyboard = InlineKeyboardMarkup::make();
    $keyboard->addRow(InlineKeyboardButton::make(text: '📂 مدیریت دسته‌بندی‌ها', callback_data: 'admin_categories'));
    $keyboard->addRow(InlineKeyboardButton::make(text: '🍽 مدیریت آیتم‌ها', callback_data: 'admin_items'));

    $bot->editMessageText(text: "⚙️ پنل مدیریت\n\nیک بخش انتخاب کنید:", reply_markup: $keyboard);
    $bot->answerCallbackQuery();
});

// List categories for admin
$bot->onCallbackQueryData('admin_categories', function (Nutgram $bot) {
    $user = $bot->get('user');
    if (!$user?->is_admin) return;

    $categories = Category::orderBy('sort_order')->get();
    $keyboard = InlineKeyboardMarkup::make();

    foreach ($categories as $cat) {
        $status = $cat->is_active ? '✅' : '❌';
        $keyboard->addRow(
            InlineKeyboardButton::make(text: "{$status} {$cat->name}", callback_data: "admin_cat:{$cat->id}"),
        );
    }

    $keyboard->addRow(InlineKeyboardButton::make(text: '➕ دسته‌بندی جدید', callback_data: 'admin_addcat'));
    $keyboard->addRow(InlineKeyboardButton::make(text: '🔴 بازگشت', callback_data: 'admin_panel'));

    $bot->editMessageText(text: "📂 دسته‌بندی‌ها:\n\n✅ = فعال | ❌ = غیرفعال", reply_markup: $keyboard);
    $bot->answerCallbackQuery();
});

// Category detail (toggle active, edit items, delete)
$bot->onCallbackQueryData('admin_cat:{id}', function (Nutgram $bot, string $id) {
    $user = $bot->get('user');
    if (!$user?->is_admin) return;

    $cat = Category::withCount('items')->find($id);
    if (!$cat) {
        $bot->answerCallbackQuery(text: '❌ یافت نشد');
        return;
    }

    $status = $cat->is_active ? 'فعال ✅' : 'غیرفعال ❌';
    $keyboard = InlineKeyboardMarkup::make();
    $keyboard->addRow(InlineKeyboardButton::make(
        text: $cat->is_active ? '❌ غیرفعال کردن' : '✅ فعال کردن',
        callback_data: "admin_togglecat:{$cat->id}",
    ));
    $keyboard->addRow(InlineKeyboardButton::make(text: '🍽 مشاهده آیتم‌ها', callback_data: "admin_catitems:{$cat->id}"));
    $keyboard->addRow(InlineKeyboardButton::make(text: '🗑 حذف دسته‌بندی', callback_data: "admin_delcat:{$cat->id}"));
    $keyboard->addRow(InlineKeyboardButton::make(text: '🔴 بازگشت', callback_data: 'admin_categories'));

    $bot->editMessageText(
        text: "📂 {$cat->name}\n\nوضعیت: {$status}\nتعداد آیتم: {$cat->items_count}",
        reply_markup: $keyboard,
    );
    $bot->answerCallbackQuery();
});

// Toggle category active
$bot->onCallbackQueryData('admin_togglecat:{id}', function (Nutgram $bot, string $id) {
    $user = $bot->get('user');
    if (!$user?->is_admin) return;

    $cat = Category::find($id);
    if ($cat) {
        $cat->update(['is_active' => !$cat->is_active]);
        $bot->answerCallbackQuery(text: $cat->is_active ? '✅ فعال شد' : '❌ غیرفعال شد');
    }

    // Refresh the category detail view
    $cat->loadCount('items');
    $status = $cat->is_active ? 'فعال ✅' : 'غیرفعال ❌';
    $keyboard = InlineKeyboardMarkup::make();
    $keyboard->addRow(InlineKeyboardButton::make(
        text: $cat->is_active ? '❌ غیرفعال کردن' : '✅ فعال کردن',
        callback_data: "admin_togglecat:{$cat->id}",
    ));
    $keyboard->addRow(InlineKeyboardButton::make(text: '🍽 مشاهده آیتم‌ها', callback_data: "admin_catitems:{$cat->id}"));
    $keyboard->addRow(InlineKeyboardButton::make(text: '🗑 حذف دسته‌بندی', callback_data: "admin_delcat:{$cat->id}"));
    $keyboard->addRow(InlineKeyboardButton::make(text: '🔴 بازگشت', callback_data: 'admin_categories'));

    $bot->editMessageText(
        text: "📂 {$cat->name}\n\nوضعیت: {$status}\nتعداد آیتم: {$cat->items_count}",
        reply_markup: $keyboard,
    );
});

// Delete category
$bot->onCallbackQueryData('admin_delcat:{id}', function (Nutgram $bot, string $id) {
    $user = $bot->get('user');
    if (!$user?->is_admin) return;

    $cat = Category::find($id);
    if ($cat) {
        $cat->delete();
        $bot->answerCallbackQuery(text: '🗑 حذف شد');
    }

    // Go back to categories list
    $categories = Category::orderBy('sort_order')->get();
    $keyboard = InlineKeyboardMarkup::make();
    foreach ($categories as $c) {
        $status = $c->is_active ? '✅' : '❌';
        $keyboard->addRow(InlineKeyboardButton::make(text: "{$status} {$c->name}", callback_data: "admin_cat:{$c->id}"));
    }
    $keyboard->addRow(InlineKeyboardButton::make(text: '➕ دسته‌بندی جدید', callback_data: 'admin_addcat'));
    $keyboard->addRow(InlineKeyboardButton::make(text: '🔴 بازگشت', callback_data: 'admin_panel'));

    $bot->editMessageText(text: "📂 دسته‌بندی‌ها:\n\n✅ = فعال | ❌ = غیرفعال", reply_markup: $keyboard);
});

// Show items in category (admin)
$bot->onCallbackQueryData('admin_catitems:{id}', function (Nutgram $bot, string $id) {
    $user = $bot->get('user');
    if (!$user?->is_admin) return;

    $cat = Category::with('items')->find($id);
    if (!$cat) return;

    $keyboard = InlineKeyboardMarkup::make();
    foreach ($cat->items as $item) {
        $status = $item->is_active ? '✅' : '❌';
        $keyboard->addRow(InlineKeyboardButton::make(
            text: "{$status} {$item->name} - " . number_format($item->price, 2) . " RM",
            callback_data: "admin_item:{$item->id}",
        ));
    }
    $keyboard->addRow(InlineKeyboardButton::make(text: "➕ آیتم جدید در {$cat->name}", callback_data: "admin_additem:{$cat->id}"));
    $keyboard->addRow(InlineKeyboardButton::make(text: '🔴 بازگشت', callback_data: "admin_cat:{$cat->id}"));

    $bot->editMessageText(text: "🍽 آیتم‌های {$cat->name}:", reply_markup: $keyboard);
    $bot->answerCallbackQuery();
});

// Item detail (admin)
$bot->onCallbackQueryData('admin_item:{id}', function (Nutgram $bot, string $id) {
    $user = $bot->get('user');
    if (!$user?->is_admin) return;

    $item = MenuItem::with('category')->find($id);
    if (!$item) return;

    $status = $item->is_active ? 'فعال ✅' : 'غیرفعال ❌';
    $keyboard = InlineKeyboardMarkup::make();
    $keyboard->addRow(InlineKeyboardButton::make(
        text: $item->is_active ? '❌ غیرفعال کردن' : '✅ فعال کردن',
        callback_data: "admin_toggleitem:{$item->id}",
    ));
    $keyboard->addRow(InlineKeyboardButton::make(text: '🗑 حذف آیتم', callback_data: "admin_delitem:{$item->id}"));
    $keyboard->addRow(InlineKeyboardButton::make(text: '🔴 بازگشت', callback_data: "admin_catitems:{$item->category_id}"));

    $bot->editMessageText(
        text: "🍽 {$item->name}\n💰 قیمت: " . number_format($item->price, 2) . " RM\nدسته: {$item->category->name}\nوضعیت: {$status}",
        reply_markup: $keyboard,
    );
    $bot->answerCallbackQuery();
});

// Toggle item active
$bot->onCallbackQueryData('admin_toggleitem:{id}', function (Nutgram $bot, string $id) {
    $user = $bot->get('user');
    if (!$user?->is_admin) return;

    $item = MenuItem::with('category')->find($id);
    if ($item) {
        $item->update(['is_active' => !$item->is_active]);
        $bot->answerCallbackQuery(text: $item->is_active ? '✅ فعال شد' : '❌ غیرفعال شد');

        $status = $item->is_active ? 'فعال ✅' : 'غیرفعال ❌';
        $keyboard = InlineKeyboardMarkup::make();
        $keyboard->addRow(InlineKeyboardButton::make(
            text: $item->is_active ? '❌ غیرفعال کردن' : '✅ فعال کردن',
            callback_data: "admin_toggleitem:{$item->id}",
        ));
        $keyboard->addRow(InlineKeyboardButton::make(text: '🗑 حذف آیتم', callback_data: "admin_delitem:{$item->id}"));
        $keyboard->addRow(InlineKeyboardButton::make(text: '🔴 بازگشت', callback_data: "admin_catitems:{$item->category_id}"));

        $bot->editMessageText(
            text: "🍽 {$item->name}\n💰 قیمت: " . number_format($item->price, 2) . " RM\nدسته: {$item->category->name}\nوضعیت: {$status}",
            reply_markup: $keyboard,
        );
    }
});

// Delete item
$bot->onCallbackQueryData('admin_delitem:{id}', function (Nutgram $bot, string $id) {
    $user = $bot->get('user');
    if (!$user?->is_admin) return;

    $item = MenuItem::find($id);
    if ($item) {
        $catId = $item->category_id;
        $item->delete();
        $bot->answerCallbackQuery(text: '🗑 حذف شد');

        // Refresh items list
        $cat = Category::with('items')->find($catId);
        $keyboard = InlineKeyboardMarkup::make();
        foreach ($cat->items as $i) {
            $status = $i->is_active ? '✅' : '❌';
            $keyboard->addRow(InlineKeyboardButton::make(
                text: "{$status} {$i->name} - " . number_format($i->price, 2) . " RM",
                callback_data: "admin_item:{$i->id}",
            ));
        }
        $keyboard->addRow(InlineKeyboardButton::make(text: "➕ آیتم جدید در {$cat->name}", callback_data: "admin_additem:{$cat->id}"));
        $keyboard->addRow(InlineKeyboardButton::make(text: '🔴 بازگشت', callback_data: "admin_cat:{$cat->id}"));

        $bot->editMessageText(text: "🍽 آیتم‌های {$cat->name}:", reply_markup: $keyboard);
    }
});

// Add category - ask for name
$bot->onCallbackQueryData('admin_addcat', function (Nutgram $bot) {
    $user = $bot->get('user');
    if (!$user?->is_admin) return;

    $bot->sendMessage("📂 نام دسته‌بندی جدید را بفرستید:\n\n(برای لغو /cancel بزنید)");
    $user->update(['state' => 'waiting_category_name']);
    $bot->answerCallbackQuery();
});

// Add item - ask for details
$bot->onCallbackQueryData('admin_additem:{catId}', function (Nutgram $bot, string $catId) {
    $user = $bot->get('user');
    if (!$user?->is_admin) return;

    $cat = Category::find($catId);
    if (!$cat) return;

    $bot->sendMessage("🍽 نام و قیمت آیتم جدید را به این فرمت بفرستید:\n\n<b>نام آیتم - قیمت</b>\n\nمثال: چلو کباب - 25.00\n\n(برای لغو /cancel بزنید)", parse_mode: 'HTML');
    $user->update(['state' => "waiting_item_details:{$catId}"]);
    $bot->answerCallbackQuery();
});

// Cancel admin action
$bot->onCommand('cancel', function (Nutgram $bot) {
    $user = $bot->get('user');
    if ($user && $user->state) {
        $user->update(['state' => null]);
        $bot->sendMessage("❌ لغو شد.");
    }
});

/*
|--------------------------------------------------------------------------
| Catch-all: back button, admin text input, unknown messages
|--------------------------------------------------------------------------
*/
$bot->onMessage(function (Nutgram $bot) {
    $user = $bot->get('user');
    if (!$user) return;

    $text = $bot->message()?->text;

    // Handle "بازگشت به منو" button
    if ($text === '🔴 بازگشت به منو') {
        if ($user->state) {
            $user->update(['state' => null]);
        }
        $bot->sendMessage(
            text: "از منوی زیر انتخاب کنید:",
            reply_markup: mainMenuKeyboard($user),
        );
        return;
    }

    // Handle admin state input (add category / add item)
    if ($user->is_admin && $user->state && $text) {
        if ($user->state === 'waiting_category_name') {
            $category = Category::create([
                'name' => $text,
                'sort_order' => Category::max('sort_order') + 1,
            ]);
            $user->update(['state' => null]);
            $bot->sendMessage("✅ دسته‌بندی «{$category->name}» اضافه شد!\n\nبرای مدیریت: /admin");
            return;
        }

        if (str_starts_with($user->state, 'waiting_item_details:')) {
            $catId = str_replace('waiting_item_details:', '', $user->state);
            $cat = Category::find($catId);
            if (!$cat) {
                $user->update(['state' => null]);
                $bot->sendMessage("❌ دسته‌بندی یافت نشد.");
                return;
            }

            $parts = explode('-', $text, 2);
            if (count($parts) < 2) {
                $bot->sendMessage("❌ فرمت اشتباه. لطفاً به فرمت زیر بفرستید:\nنام آیتم - قیمت\n\nمثال: چلو کباب - 25.00");
                return;
            }

            $name = trim($parts[0]);
            $price = (float) trim($parts[1]);

            if ($price <= 0) {
                $bot->sendMessage("❌ قیمت باید عدد مثبت باشد.");
                return;
            }

            $item = MenuItem::create([
                'category_id' => $cat->id,
                'name' => $name,
                'price' => $price,
                'sort_order' => MenuItem::where('category_id', $cat->id)->max('sort_order') + 1,
            ]);

            $user->update(['state' => null]);
            $bot->sendMessage("✅ «{$item->name}» با قیمت " . number_format($price, 2) . " RM به {$cat->name} اضافه شد!\n\nبرای مدیریت: /admin");
            return;
        }
    }

    // Unknown message - respond to registered users
    if ($user->is_registered) {
        $bot->sendMessage(
            text: "❌ متوجه نشدم! لطفاً از منوی زیر استفاده کنید:",
            reply_markup: mainMenuKeyboard($user),
        );
    }
});

/*
|--------------------------------------------------------------------------
| Helper Functions
|--------------------------------------------------------------------------
*/

function showCategories(Nutgram $bot): void
{
    $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
    $keyboard = InlineKeyboardMarkup::make();

    foreach ($categories as $category) {
        $keyboard->addRow(
            InlineKeyboardButton::make(text: "🔵 {$category->name}", callback_data: "category:{$category->id}"),
        );
    }

    $user = $bot->get('user');
    $cart = Order::where('user_id', $user->id)->where('status', 'cart')->withCount('items')->first();

    if ($cart && $cart->items_count > 0) {
        $keyboard->addRow(
            InlineKeyboardButton::make(
                text: '🔵 سبد خرید (' . number_format($cart->total_price, 2) . ' RM)',
                callback_data: 'view_cart',
            ),
        );
    }

    $bot->sendMessage(
        text: "🍽 منوی امیر کترینگ\n\nیک دسته‌بندی انتخاب کنید:",
        reply_markup: $keyboard,
    );
}

function showAdminPanel(Nutgram $bot): void
{
    $keyboard = InlineKeyboardMarkup::make();
    $keyboard->addRow(InlineKeyboardButton::make(text: '📂 مدیریت دسته‌بندی‌ها', callback_data: 'admin_categories'));
    $keyboard->addRow(InlineKeyboardButton::make(text: '🍽 مدیریت آیتم‌ها', callback_data: 'admin_items'));

    $bot->sendMessage(text: "⚙️ پنل مدیریت\n\nیک بخش انتخاب کنید:", reply_markup: $keyboard);
}

// Admin items list (all categories)
$bot->onCallbackQueryData('admin_items', function (Nutgram $bot) {
    $user = $bot->get('user');
    if (!$user?->is_admin) return;

    $categories = Category::orderBy('sort_order')->get();
    $keyboard = InlineKeyboardMarkup::make();

    foreach ($categories as $cat) {
        $keyboard->addRow(InlineKeyboardButton::make(
            text: "📂 {$cat->name}",
            callback_data: "admin_catitems:{$cat->id}",
        ));
    }
    $keyboard->addRow(InlineKeyboardButton::make(text: '🔴 بازگشت', callback_data: 'admin_panel'));

    $bot->editMessageText(text: "🍽 یک دسته‌بندی انتخاب کنید تا آیتم‌هایش را ببینید:", reply_markup: $keyboard);
    $bot->answerCallbackQuery();
});

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
            $bot->sendMessage(
                text: $text,
                chat_id: $admin->telegram_id,
            );

            if ($customer->latitude && $customer->longitude) {
                $bot->sendLocation(
                    latitude: $customer->latitude,
                    longitude: $customer->longitude,
                    chat_id: $admin->telegram_id,
                );
            }

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
