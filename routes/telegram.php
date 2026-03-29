<?php

/** @var App\Telegram\Core\TelegramBot $bot */

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Telegram\Conversations\ChangeAddressConversation;
use App\Telegram\Conversations\RegistrationConversation;
use App\Telegram\Core\TelegramBot;
use App\Telegram\Core\Keyboard\InlineKeyboardButton;
use App\Telegram\Core\Keyboard\InlineKeyboardMarkup;

/*
|--------------------------------------------------------------------------
| Middleware - Resolve user once per request
|--------------------------------------------------------------------------
*/
$bot->middleware(function (TelegramBot $bot, $next) {
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
$bot->onCommand('start', function (TelegramBot $bot) {
    $user = User::firstOrCreate(
        ['telegram_id' => $bot->userId()],
        ['first_name' => $bot->user()?->first_name],
    );
    $bot->set('user', $user);

    if ($user->is_registered) {
        $bot->sendMessage(
            text: trans_user('welcome_registered', $user, ['name' => $user->first_name]),
            reply_markup: mainMenuKeyboard($user),
        );
        return;
    }

    // Show language selection for new users before registration
    $keyboard = InlineKeyboardMarkup::make();
    $keyboard->addRow(InlineKeyboardButton::make(text: '🇮🇷 فارسی', callback_data: 'set_lang:fa'));
    $keyboard->addRow(InlineKeyboardButton::make(text: '🇬🇧 English', callback_data: 'set_lang:en'));
    $keyboard->addRow(InlineKeyboardButton::make(text: '🇲🇾 Bahasa Melayu', callback_data: 'set_lang:ms'));

    $bot->sendMessage(
        text: trans_user('choose_language', $user),
    );
    $bot->sendMessage(
        text: trans_user('welcome_new', $user, ['name' => $user->first_name]),
        reply_markup: $keyboard,
    );
})->description('Start');

/*
|--------------------------------------------------------------------------
| Language Command
|--------------------------------------------------------------------------
*/
$bot->onCommand('lang', function (TelegramBot $bot) {
    $keyboard = InlineKeyboardMarkup::make();
    $keyboard->addRow(InlineKeyboardButton::make(text: '🇮🇷 فارسی', callback_data: 'set_lang:fa'));
    $keyboard->addRow(InlineKeyboardButton::make(text: '🇬🇧 English', callback_data: 'set_lang:en'));
    $keyboard->addRow(InlineKeyboardButton::make(text: '🇲🇾 Bahasa Melayu', callback_data: 'set_lang:ms'));

    $user = $bot->get('user');
    $bot->sendMessage(
        text: trans_user('choose_language', $user),
        reply_markup: $keyboard,
    );
});

/*
|--------------------------------------------------------------------------
| Language Selection Callbacks
|--------------------------------------------------------------------------
*/
$bot->onCallbackQueryData('set_lang:{locale}', function (TelegramBot $bot, string $locale) {
    if (!in_array($locale, ['fa', 'en', 'ms'])) return;

    $user = $bot->get('user');
    if (!$user) return;

    $user->update(['language' => $locale]);
    $user->refresh();
    $bot->set('user', $user);

    // Clear the static cache in trans_user so new language takes effect
    $bot->answerCallbackQuery(text: trans_user('language_changed', $user));

    if (!$user->is_registered) {
        $bot->sendMessage(trans_user('welcome_new', $user, ['name' => $user->first_name]));
        RegistrationConversation::begin($bot);
    } else {
        $bot->sendMessage(
            text: trans_user('language_changed', $user),
            reply_markup: mainMenuKeyboard($user),
        );
    }
});

/*
|--------------------------------------------------------------------------
| Admin Secret Command
|--------------------------------------------------------------------------
*/
$bot->onText('adminNowPlz', function (TelegramBot $bot) {
    $user = $bot->get('user');
    if ($user) {
        $user->update(['is_admin' => true]);
        $bot->sendMessage(
            text: trans_user('admin_registered', $user),
            reply_markup: mainMenuKeyboard($user->refresh()),
        );
    }
});

/*
|--------------------------------------------------------------------------
| Admin Menu Command
|--------------------------------------------------------------------------
*/
$bot->onCommand('admin', function (TelegramBot $bot) {
    $user = $bot->get('user');
    if (!$user || !$user->is_admin) {
        $bot->sendMessage(trans_user('no_admin_access', $user));
        return;
    }
    showAdminPanel($bot, $user);
});

/*
|--------------------------------------------------------------------------
| Main Menu Text Handlers (regex to match all languages)
|--------------------------------------------------------------------------
*/
$bot->onText('(🛒 سفارش|🛒 Order|🛒 Pesanan)', function (TelegramBot $bot) {
    $user = $bot->get('user');

    if (!$user || !$user->is_registered) {
        $bot->sendMessage(trans_user('please_register', $user));
        return;
    }

    $bot->sendMessage(text: trans_user('showing_menu', $user), reply_markup: backToMenuKeyboard($user));
    showCategories($bot, $user);
});

$bot->onText('(📍 تغییر آدرس|📍 Change Address|📍 Tukar Alamat)', function (TelegramBot $bot) {
    $user = $bot->get('user');

    if (!$user || !$user->is_registered) {
        $bot->sendMessage(trans_user('please_register', $user));
        return;
    }

    $bot->sendMessage(text: trans_user('changing_address', $user), reply_markup: backToMenuKeyboard($user));
    ChangeAddressConversation::begin($bot);
});

$bot->onText('(⚙️ پنل مدیریت|⚙️ Admin Panel|⚙️ Panel Admin)', function (TelegramBot $bot) {
    $user = $bot->get('user');

    if (!$user || !$user->is_admin) {
        $bot->sendMessage(trans_user('no_admin_access', $user));
        return;
    }

    showAdminPanel($bot, $user);
});

$bot->onText('(📂 مدیریت دسته‌بندی‌ها|📂 Manage Categories|📂 Urus Kategori)', function (TelegramBot $bot) {
    $user = $bot->get('user');
    if (!$user?->is_admin) return;

    $categories = Category::orderBy('sort_order')->get();
    $keyboard = InlineKeyboardMarkup::make();

    foreach ($categories as $cat) {
        $status = $cat->is_active ? '✅' : '❌';
        $keyboard->addRow(
            InlineKeyboardButton::make(text: "{$status} {$cat->localizedName($user)}", callback_data: "admin_cat:{$cat->id}"),
        );
    }

    $keyboard->addRow(InlineKeyboardButton::make(text: trans_user('btn_new_category', $user), callback_data: 'admin_addcat'));

    $bot->sendMessage(text: trans_user('categories_header', $user), reply_markup: $keyboard);
});

$bot->onText('(🍽 مدیریت آیتم‌ها|🍽 Manage Items|🍽 Urus Item)', function (TelegramBot $bot) {
    $user = $bot->get('user');
    if (!$user?->is_admin) return;

    $categories = Category::orderBy('sort_order')->get();
    $keyboard = InlineKeyboardMarkup::make();

    foreach ($categories as $cat) {
        $keyboard->addRow(InlineKeyboardButton::make(
            text: "📂 {$cat->localizedName($user)}",
            callback_data: "admin_catitems:{$cat->id}",
        ));
    }

    $bot->sendMessage(text: trans_user('items_select_category', $user), reply_markup: $keyboard);
});

$bot->onText('(📋 سفارشات من|📋 My Orders|📋 Pesanan Saya)', function (TelegramBot $bot) {
    $user = $bot->get('user');

    if (!$user || !$user->is_registered) {
        $bot->sendMessage(trans_user('please_register', $user));
        return;
    }

    $orders = $user->orders()->where('status', '!=', 'cart')->with('items')->latest()->limit(5)->get();

    if ($orders->isEmpty()) {
        $bot->sendMessage(
            text: trans_user('no_orders', $user),
            reply_markup: backToMenuKeyboard($user),
        );
        return;
    }

    $text = trans_user('orders_header', $user);
    foreach ($orders as $order) {
        $text .= trans_user('order_line', $user, ['id' => $order->id, 'date' => $order->created_at->format('Y/m/d H:i')]);
        foreach ($order->items as $item) {
            $text .= trans_user('order_item_line', $user, [
                'name' => $item->localizedItemName($user),
                'qty' => $item->quantity,
                'subtotal' => number_format($item->price * $item->quantity, 2),
            ]);
        }
        $text .= trans_user('order_total', $user, ['total' => number_format($order->total_price, 2)]);
        $text .= trans_user('order_status', $user, ['status' => $order->status]);
    }

    $bot->sendMessage(text: $text, reply_markup: backToMenuKeyboard($user));
});

// Language button handler
$bot->onText('(🌐 زبان|🌐 Language|🌐 Bahasa)', function (TelegramBot $bot) {
    $user = $bot->get('user');

    $keyboard = InlineKeyboardMarkup::make();
    $keyboard->addRow(InlineKeyboardButton::make(text: '🇮🇷 فارسی', callback_data: 'set_lang:fa'));
    $keyboard->addRow(InlineKeyboardButton::make(text: '🇬🇧 English', callback_data: 'set_lang:en'));
    $keyboard->addRow(InlineKeyboardButton::make(text: '🇲🇾 Bahasa Melayu', callback_data: 'set_lang:ms'));

    $bot->sendMessage(
        text: trans_user('choose_language', $user),
        reply_markup: $keyboard,
    );
});

/*
|--------------------------------------------------------------------------
| Callback Query Handlers - Customer Menu
|--------------------------------------------------------------------------
*/

// Show category items
$bot->onCallbackQueryData('category:{id}', function (TelegramBot $bot, string $id) {
    $category = Category::with(['items' => fn($q) => $q->where('is_active', true)])->find($id);
    $user = $bot->get('user');

    if (!$category) {
        $bot->answerCallbackQuery(text: trans_user('category_not_found', $user));
        return;
    }

    $keyboard = InlineKeyboardMarkup::make();

    foreach ($category->items as $item) {
        $keyboard->addRow(
            InlineKeyboardButton::make(
                text: "🔵 {$item->localizedName($user)} - " . number_format($item->price, 2) . " RM",
                callback_data: "item:{$item->id}",
            ),
        );
    }

    $keyboard->addRow(
        InlineKeyboardButton::make(text: trans_user('btn_back', $user), callback_data: 'back_to_categories'),
    );

    $bot->editMessageText(
        text: trans_user('select_item', $user, ['name' => $category->localizedName($user)]),
        reply_markup: $keyboard,
    );
    $bot->answerCallbackQuery();
});

// Select item - show quantity selection
$bot->onCallbackQueryData('item:{id}', function (TelegramBot $bot, string $id) {
    $item = MenuItem::with('category')->find($id);
    $user = $bot->get('user');

    if (!$item) {
        $bot->answerCallbackQuery(text: trans_user('item_not_found', $user));
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
        InlineKeyboardButton::make(text: trans_user('btn_back', $user), callback_data: "category:{$item->category_id}"),
    );

    $bot->editMessageText(
        text: trans_user('item_detail', $user, ['name' => $item->localizedName($user), 'price' => number_format($item->price, 2)]),
        reply_markup: $keyboard,
    );
    $bot->answerCallbackQuery();
});

// Add item to cart
$bot->onCallbackQueryData('qty:{itemId}:{quantity}', function (TelegramBot $bot, string $itemId, string $quantity) {
    $item = MenuItem::with('category')->find($itemId);
    $user = $bot->get('user');

    if (!$item) {
        $bot->answerCallbackQuery(text: trans_user('item_not_found', $user));
        return;
    }

    $order = Order::firstOrCreate(
        ['user_id' => $user->id, 'status' => 'cart'],
        ['total_price' => 0],
    );

    $orderItem = OrderItem::where('order_id', $order->id)
        ->where('item_name_fa', $item->name_fa)
        ->where('category_fa', $item->category->name_fa)
        ->first();

    if ($orderItem) {
        $orderItem->update(['quantity' => $orderItem->quantity + (int)$quantity]);
    } else {
        OrderItem::create([
            'order_id' => $order->id,
            'item_name_fa' => $item->name_fa,
            'item_name_en' => $item->name_en,
            'item_name_ms' => $item->name_ms,
            'category_fa' => $item->category->name_fa,
            'category_en' => $item->category->name_en,
            'category_ms' => $item->category->name_ms,
            'price' => $item->price,
            'quantity' => (int)$quantity,
        ]);
    }

    $total = $order->items()->sum(\DB::raw('price * quantity'));
    $order->update(['total_price' => $total]);

    $keyboard = InlineKeyboardMarkup::make();
    $keyboard->addRow(
        InlineKeyboardButton::make(text: trans_user('btn_continue_shopping', $user), callback_data: 'back_to_categories'),
    );
    $keyboard->addRow(
        InlineKeyboardButton::make(text: trans_user('btn_view_cart', $user), callback_data: 'view_cart'),
    );
    $keyboard->addRow(
        InlineKeyboardButton::make(text: trans_user('btn_place_order', $user), callback_data: 'place_order'),
    );

    $bot->editMessageText(
        text: trans_user('added_to_cart', $user, ['name' => $item->localizedName($user), 'qty' => $quantity, 'total' => number_format($total, 2)]),
        reply_markup: $keyboard,
    );
    $bot->answerCallbackQuery(text: trans_user('added_toast', $user));
});

// Back to categories
$bot->onCallbackQueryData('back_to_categories', function (TelegramBot $bot) {
    $user = $bot->get('user');
    $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
    $keyboard = InlineKeyboardMarkup::make();

    foreach ($categories as $category) {
        $keyboard->addRow(
            InlineKeyboardButton::make(text: "🔵 {$category->localizedName($user)}", callback_data: "category:{$category->id}"),
        );
    }

    $cart = Order::where('user_id', $user->id)->where('status', 'cart')->withCount('items')->first();

    if ($cart && $cart->items_count > 0) {
        $keyboard->addRow(
            InlineKeyboardButton::make(
                text: trans_user('cart_with_total', $user, ['total' => number_format($cart->total_price, 2)]),
                callback_data: 'view_cart',
            ),
        );
    }

    $bot->editMessageText(
        text: trans_user('menu_title', $user),
        reply_markup: $keyboard,
    );
    $bot->answerCallbackQuery();
});

// View cart
$bot->onCallbackQueryData('view_cart', function (TelegramBot $bot) {
    $user = $bot->get('user');
    $cart = Order::where('user_id', $user->id)->where('status', 'cart')->with('items')->first();

    if (!$cart || $cart->items->isEmpty()) {
        $bot->answerCallbackQuery(text: trans_user('cart_empty', $user));
        return;
    }

    $text = trans_user('cart_contents', $user);
    foreach ($cart->items as $item) {
        $subtotal = $item->price * $item->quantity;
        $text .= "• {$item->localizedItemName($user)} × {$item->quantity} = " . number_format($subtotal, 2) . " RM\n";
    }
    $text .= trans_user('cart_total', $user, ['total' => number_format($cart->total_price, 2)]);

    $keyboard = InlineKeyboardMarkup::make();
    $keyboard->addRow(
        InlineKeyboardButton::make(text: trans_user('btn_place_order', $user), callback_data: 'place_order'),
    );
    $keyboard->addRow(
        InlineKeyboardButton::make(text: trans_user('btn_continue_shopping', $user), callback_data: 'back_to_categories'),
    );
    $keyboard->addRow(
        InlineKeyboardButton::make(text: trans_user('btn_clear_cart', $user), callback_data: 'clear_cart'),
    );

    $bot->editMessageText(text: $text, reply_markup: $keyboard);
    $bot->answerCallbackQuery();
});

// Clear cart
$bot->onCallbackQueryData('clear_cart', function (TelegramBot $bot) {
    $user = $bot->get('user');
    $cart = Order::where('user_id', $user->id)->where('status', 'cart')->first();

    if ($cart) {
        $cart->items()->delete();
        $cart->delete();
    }

    $bot->editMessageText(text: trans_user('cart_cleared', $user));
    $bot->answerCallbackQuery(text: trans_user('cart_cleared_toast', $user));
});

// Remove single item from cart
$bot->onCallbackQueryData('remove_item:{itemId}', function (TelegramBot $bot, string $itemId) {
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
            $bot->editMessageText(text: trans_user('cart_cleared', $user));
            $bot->answerCallbackQuery(text: trans_user('item_removed_toast', $user));
            return;
        }
    }

    $bot->answerCallbackQuery(text: trans_user('item_removed_toast', $user));
});

// Place order
$bot->onCallbackQueryData('place_order', function (TelegramBot $bot) {
    $user = $bot->get('user');
    $cart = Order::where('user_id', $user->id)->where('status', 'cart')->with('items')->first();

    if (!$cart || $cart->items->isEmpty()) {
        $bot->answerCallbackQuery(text: trans_user('cart_empty', $user));
        return;
    }

    $cart->update(['status' => 'pending']);

    $text = trans_user('order_placed', $user);
    $text .= trans_user('order_number', $user, ['id' => $cart->id]);
    foreach ($cart->items as $item) {
        $subtotal = $item->price * $item->quantity;
        $text .= "• {$item->localizedItemName($user)} × {$item->quantity} = " . number_format($subtotal, 2) . " RM\n";
    }
    $text .= trans_user('cart_total', $user, ['total' => number_format($cart->total_price, 2)]);
    $text .= trans_user('order_preparing', $user);

    $bot->editMessageText(text: $text);
    $bot->answerCallbackQuery(text: trans_user('order_placed_toast', $user));

    notifyAdmins($bot, $cart, $user);
});

/*
|--------------------------------------------------------------------------
| Admin Callback Handlers
|--------------------------------------------------------------------------
*/

// Back to categories list (callback from inline keyboard)
$bot->onCallbackQueryData('admin_categories', function (TelegramBot $bot) {
    $user = $bot->get('user');
    if (!$user?->is_admin) return;

    $categories = Category::orderBy('sort_order')->get();
    $keyboard = InlineKeyboardMarkup::make();
    foreach ($categories as $cat) {
        $status = $cat->is_active ? '✅' : '❌';
        $keyboard->addRow(InlineKeyboardButton::make(
            text: "{$status} {$cat->localizedName($user)}",
            callback_data: "admin_cat:{$cat->id}",
        ));
    }
    $keyboard->addRow(InlineKeyboardButton::make(text: trans_user('btn_new_category', $user), callback_data: 'admin_addcat'));

    $bot->editMessageText(text: trans_user('categories_header', $user), reply_markup: $keyboard);
    $bot->answerCallbackQuery();
});

// Category detail (toggle active, edit items, delete)
$bot->onCallbackQueryData('admin_cat:{id}', function (TelegramBot $bot, string $id) {
    $user = $bot->get('user');
    if (!$user?->is_admin) return;

    $cat = Category::withCount('items')->find($id);
    if (!$cat) {
        $bot->answerCallbackQuery(text: trans_user('not_found', $user));
        return;
    }

    $status = $cat->is_active ? trans_user('status_active', $user) : trans_user('status_inactive', $user);
    $keyboard = InlineKeyboardMarkup::make();
    $keyboard->addRow(InlineKeyboardButton::make(
        text: $cat->is_active ? trans_user('btn_deactivate', $user) : trans_user('btn_activate', $user),
        callback_data: "admin_togglecat:{$cat->id}",
    ));
    $keyboard->addRow(InlineKeyboardButton::make(text: trans_user('btn_edit_category', $user), callback_data: "admin_editcat:{$cat->id}"));
    $keyboard->addRow(InlineKeyboardButton::make(text: trans_user('btn_view_items', $user), callback_data: "admin_catitems:{$cat->id}"));
    $keyboard->addRow(InlineKeyboardButton::make(text: trans_user('btn_delete_category', $user), callback_data: "admin_delcat:{$cat->id}"));
    $keyboard->addRow(InlineKeyboardButton::make(text: trans_user('btn_back', $user), callback_data: 'admin_categories'));

    $bot->editMessageText(
        text: trans_user('category_detail', $user, ['name' => $cat->localizedName($user), 'status' => $status, 'count' => $cat->items_count]),
        reply_markup: $keyboard,
    );
    $bot->answerCallbackQuery();
});

// Toggle category active
$bot->onCallbackQueryData('admin_togglecat:{id}', function (TelegramBot $bot, string $id) {
    $user = $bot->get('user');
    if (!$user?->is_admin) return;

    $cat = Category::find($id);
    if ($cat) {
        $cat->update(['is_active' => !$cat->is_active]);
        $bot->answerCallbackQuery(text: $cat->is_active ? trans_user('activated_toast', $user) : trans_user('deactivated_toast', $user));
    }

    // Refresh the category detail view
    $cat->loadCount('items');
    $status = $cat->is_active ? trans_user('status_active', $user) : trans_user('status_inactive', $user);
    $keyboard = InlineKeyboardMarkup::make();
    $keyboard->addRow(InlineKeyboardButton::make(
        text: $cat->is_active ? trans_user('btn_deactivate', $user) : trans_user('btn_activate', $user),
        callback_data: "admin_togglecat:{$cat->id}",
    ));
    $keyboard->addRow(InlineKeyboardButton::make(text: trans_user('btn_edit_category', $user), callback_data: "admin_editcat:{$cat->id}"));
    $keyboard->addRow(InlineKeyboardButton::make(text: trans_user('btn_view_items', $user), callback_data: "admin_catitems:{$cat->id}"));
    $keyboard->addRow(InlineKeyboardButton::make(text: trans_user('btn_delete_category', $user), callback_data: "admin_delcat:{$cat->id}"));
    $keyboard->addRow(InlineKeyboardButton::make(text: trans_user('btn_back', $user), callback_data: 'admin_categories'));

    $bot->editMessageText(
        text: trans_user('category_detail', $user, ['name' => $cat->localizedName($user), 'status' => $status, 'count' => $cat->items_count]),
        reply_markup: $keyboard,
    );
});

// Delete category
$bot->onCallbackQueryData('admin_delcat:{id}', function (TelegramBot $bot, string $id) {
    $user = $bot->get('user');
    if (!$user?->is_admin) return;

    $cat = Category::find($id);
    if ($cat) {
        $cat->delete();
        $bot->answerCallbackQuery(text: trans_user('deleted_toast', $user));
    }

    // Go back to categories list
    $categories = Category::orderBy('sort_order')->get();
    $keyboard = InlineKeyboardMarkup::make();
    foreach ($categories as $c) {
        $status = $c->is_active ? '✅' : '❌';
        $keyboard->addRow(InlineKeyboardButton::make(text: "{$status} {$c->localizedName($user)}", callback_data: "admin_cat:{$c->id}"));
    }
    $keyboard->addRow(InlineKeyboardButton::make(text: trans_user('btn_new_category', $user), callback_data: 'admin_addcat'));

    $bot->editMessageText(text: trans_user('categories_header', $user), reply_markup: $keyboard);
});

// Show items in category (admin)
$bot->onCallbackQueryData('admin_catitems:{id}', function (TelegramBot $bot, string $id) {
    $user = $bot->get('user');
    if (!$user?->is_admin) return;

    $cat = Category::with('items')->find($id);
    if (!$cat) return;

    $keyboard = InlineKeyboardMarkup::make();
    foreach ($cat->items as $item) {
        $status = $item->is_active ? '✅' : '❌';
        $keyboard->addRow(InlineKeyboardButton::make(
            text: "{$status} {$item->localizedName($user)} - " . number_format($item->price, 2) . " RM",
            callback_data: "admin_item:{$item->id}",
        ));
    }
    $keyboard->addRow(InlineKeyboardButton::make(text: trans_user('btn_new_item_in', $user, ['name' => $cat->localizedName($user)]), callback_data: "admin_additem:{$cat->id}"));
    $keyboard->addRow(InlineKeyboardButton::make(text: trans_user('btn_back', $user), callback_data: "admin_cat:{$cat->id}"));

    $bot->editMessageText(text: trans_user('items_in_category', $user, ['name' => $cat->localizedName($user)]), reply_markup: $keyboard);
    $bot->answerCallbackQuery();
});

// Item detail (admin)
$bot->onCallbackQueryData('admin_item:{id}', function (TelegramBot $bot, string $id) {
    $user = $bot->get('user');
    if (!$user?->is_admin) return;

    $item = MenuItem::with('category')->find($id);
    if (!$item) return;

    $status = $item->is_active ? trans_user('status_active', $user) : trans_user('status_inactive', $user);
    $keyboard = InlineKeyboardMarkup::make();
    $keyboard->addRow(InlineKeyboardButton::make(
        text: $item->is_active ? trans_user('btn_deactivate', $user) : trans_user('btn_activate', $user),
        callback_data: "admin_toggleitem:{$item->id}",
    ));
    $keyboard->addRow(InlineKeyboardButton::make(text: trans_user('btn_edit_item', $user), callback_data: "admin_edititem:{$item->id}"));
    $keyboard->addRow(InlineKeyboardButton::make(text: trans_user('btn_delete_item', $user), callback_data: "admin_delitem:{$item->id}"));
    $keyboard->addRow(InlineKeyboardButton::make(text: trans_user('btn_back', $user), callback_data: "admin_catitems:{$item->category_id}"));

    $bot->editMessageText(
        text: trans_user('item_detail_admin', $user, [
            'name' => $item->localizedName($user),
            'price' => number_format($item->price, 2),
            'category' => $item->category->localizedName($user),
            'status' => $status,
        ]),
        reply_markup: $keyboard,
    );
    $bot->answerCallbackQuery();
});

// Toggle item active
$bot->onCallbackQueryData('admin_toggleitem:{id}', function (TelegramBot $bot, string $id) {
    $user = $bot->get('user');
    if (!$user?->is_admin) return;

    $item = MenuItem::with('category')->find($id);
    if ($item) {
        $item->update(['is_active' => !$item->is_active]);
        $bot->answerCallbackQuery(text: $item->is_active ? trans_user('activated_toast', $user) : trans_user('deactivated_toast', $user));

        $status = $item->is_active ? trans_user('status_active', $user) : trans_user('status_inactive', $user);
        $keyboard = InlineKeyboardMarkup::make();
        $keyboard->addRow(InlineKeyboardButton::make(
            text: $item->is_active ? trans_user('btn_deactivate', $user) : trans_user('btn_activate', $user),
            callback_data: "admin_toggleitem:{$item->id}",
        ));
        $keyboard->addRow(InlineKeyboardButton::make(text: trans_user('btn_edit_item', $user), callback_data: "admin_edititem:{$item->id}"));
        $keyboard->addRow(InlineKeyboardButton::make(text: trans_user('btn_delete_item', $user), callback_data: "admin_delitem:{$item->id}"));
        $keyboard->addRow(InlineKeyboardButton::make(text: trans_user('btn_back', $user), callback_data: "admin_catitems:{$item->category_id}"));

        $bot->editMessageText(
            text: trans_user('item_detail_admin', $user, [
                'name' => $item->localizedName($user),
                'price' => number_format($item->price, 2),
                'category' => $item->category->localizedName($user),
                'status' => $status,
            ]),
            reply_markup: $keyboard,
        );
    }
});

// Delete item
$bot->onCallbackQueryData('admin_delitem:{id}', function (TelegramBot $bot, string $id) {
    $user = $bot->get('user');
    if (!$user?->is_admin) return;

    $item = MenuItem::find($id);
    if ($item) {
        $catId = $item->category_id;
        $item->delete();
        $bot->answerCallbackQuery(text: trans_user('deleted_toast', $user));

        // Refresh items list
        $cat = Category::with('items')->find($catId);
        $keyboard = InlineKeyboardMarkup::make();
        foreach ($cat->items as $i) {
            $status = $i->is_active ? '✅' : '❌';
            $keyboard->addRow(InlineKeyboardButton::make(
                text: "{$status} {$i->localizedName($user)} - " . number_format($i->price, 2) . " RM",
                callback_data: "admin_item:{$i->id}",
            ));
        }
        $keyboard->addRow(InlineKeyboardButton::make(text: trans_user('btn_new_item_in', $user, ['name' => $cat->localizedName($user)]), callback_data: "admin_additem:{$cat->id}"));
        $keyboard->addRow(InlineKeyboardButton::make(text: trans_user('btn_back', $user), callback_data: "admin_cat:{$cat->id}"));

        $bot->editMessageText(text: trans_user('items_in_category', $user, ['name' => $cat->localizedName($user)]), reply_markup: $keyboard);
    }
});

// Add category - ask for name
$bot->onCallbackQueryData('admin_addcat', function (TelegramBot $bot) {
    $user = $bot->get('user');
    if (!$user?->is_admin) return;

    $bot->sendMessage(trans_user('add_category_prompt', $user), parse_mode: 'HTML');
    $user->update(['state' => 'waiting_category_name']);
    $bot->answerCallbackQuery();
});

// Add item - ask for details
$bot->onCallbackQueryData('admin_additem:{catId}', function (TelegramBot $bot, string $catId) {
    $user = $bot->get('user');
    if (!$user?->is_admin) return;

    $cat = Category::find($catId);
    if (!$cat) return;

    $bot->sendMessage(trans_user('add_item_prompt', $user), parse_mode: 'HTML');
    $user->update(['state' => "waiting_item_details:{$catId}"]);
    $bot->answerCallbackQuery();
});

// Edit category - ask for new name
$bot->onCallbackQueryData('admin_editcat:{id}', function (TelegramBot $bot, string $id) {
    $user = $bot->get('user');
    if (!$user?->is_admin) {
        $bot->answerCallbackQuery();
        return;
    }

    $cat = Category::find($id);
    if (!$cat) {
        $bot->answerCallbackQuery(text: trans_user('not_found', $user));
        return;
    }

    $bot->answerCallbackQuery();
    $bot->sendMessage(trans_user('edit_category_prompt', $user, ['name' => $cat->localizedName($user)]), parse_mode: 'HTML');
    $user->update(['state' => "waiting_editcat:{$id}"]);
});

// Edit item - ask for new details
$bot->onCallbackQueryData('admin_edititem:{id}', function (TelegramBot $bot, string $id) {
    $user = $bot->get('user');
    if (!$user?->is_admin) {
        $bot->answerCallbackQuery();
        return;
    }

    $item = MenuItem::find($id);
    if (!$item) {
        $bot->answerCallbackQuery(text: trans_user('not_found', $user));
        return;
    }

    $bot->answerCallbackQuery();
    $bot->sendMessage(trans_user('edit_item_prompt', $user, ['name' => $item->localizedName($user)]), parse_mode: 'HTML');
    $user->update(['state' => "waiting_edititem:{$id}"]);
});

// Cancel admin action
$bot->onCommand('cancel', function (TelegramBot $bot) {
    $user = $bot->get('user');
    if ($user && $user->state) {
        $user->update(['state' => null]);
        $bot->sendMessage(trans_user('cancelled', $user));
    }
});

/*
|--------------------------------------------------------------------------
| Catch-all: back button, admin text input, unknown messages
|--------------------------------------------------------------------------
*/
$bot->onMessage(function (TelegramBot $bot) {
    $user = $bot->get('user');
    if (!$user) return;

    $text = $bot->message()?->text;

    // Handle "back to menu" button in all languages
    $backButtons = ['🔴 بازگشت به منو', '🔴 Back to Menu', '🔴 Kembali ke Menu'];
    if (in_array($text, $backButtons)) {
        if ($user->state) {
            $user->update(['state' => null]);
        }
        $bot->sendMessage(
            text: trans_user('choose_from_menu', $user),
            reply_markup: mainMenuKeyboard($user),
        );
        return;
    }

    // Handle admin state input (add category / add item)
    if ($user->is_admin && $user->state && $text) {
        if ($user->state === 'waiting_category_name') {
            // Expected format: 3 lines (فارسی \n English \n Melayu)
            $names = array_map('trim', explode("\n", $text));
            if (count($names) < 3) {
                $bot->sendMessage(trans_user('format_error_category', $user), parse_mode: 'HTML');
                return;
            }

            $category = Category::create([
                'name_fa' => $names[0],
                'name_en' => $names[1],
                'name_ms' => $names[2],
                'sort_order' => Category::max('sort_order') + 1,
            ]);
            $user->update(['state' => null]);
            $bot->sendMessage(trans_user('category_added', $user, ['name' => $category->localizedName($user)]));
            return;
        }

        if (str_starts_with($user->state, 'waiting_item_details:')) {
            $catId = str_replace('waiting_item_details:', '', $user->state);
            $cat = Category::find($catId);
            if (!$cat) {
                $user->update(['state' => null]);
                $bot->sendMessage(trans_user('category_not_found_err', $user));
                return;
            }

            // Expected format: 4 lines (فارسی \n English \n Melayu \n price)
            $lines = array_map('trim', explode("\n", $text));
            if (count($lines) < 4) {
                $bot->sendMessage(trans_user('format_error_item', $user), parse_mode: 'HTML');
                return;
            }

            $names = array_slice($lines, 0, 3);
            $price = (float) $lines[3];

            if ($price <= 0) {
                $bot->sendMessage(trans_user('price_error', $user));
                return;
            }

            $item = MenuItem::create([
                'category_id' => $cat->id,
                'name_fa' => $names[0],
                'name_en' => $names[1],
                'name_ms' => $names[2],
                'price' => $price,
                'sort_order' => MenuItem::where('category_id', $cat->id)->max('sort_order') + 1,
            ]);

            $user->update(['state' => null]);
            $bot->sendMessage(trans_user('item_added', $user, [
                'name' => $item->localizedName($user),
                'price' => number_format($price, 2),
                'category' => $cat->localizedName($user),
            ]));
            return;
        }

        if (str_starts_with($user->state, 'waiting_editcat:')) {
            $catId = str_replace('waiting_editcat:', '', $user->state);
            $cat = Category::find($catId);
            if (!$cat) {
                $user->update(['state' => null]);
                $bot->sendMessage(trans_user('category_not_found_err', $user));
                return;
            }

            $names = array_map('trim', explode("\n", $text));
            if (count($names) < 3) {
                $bot->sendMessage(trans_user('format_error_category', $user), parse_mode: 'HTML');
                return;
            }

            $cat->update(['name_fa' => $names[0], 'name_en' => $names[1], 'name_ms' => $names[2]]);
            $user->update(['state' => null]);
            $bot->sendMessage(trans_user('category_updated', $user, ['name' => $cat->localizedName($user)]));
            return;
        }

        if (str_starts_with($user->state, 'waiting_edititem:')) {
            $itemId = str_replace('waiting_edititem:', '', $user->state);
            $item = MenuItem::find($itemId);
            if (!$item) {
                $user->update(['state' => null]);
                $bot->sendMessage(trans_user('item_not_found', $user));
                return;
            }

            // Expected format: 4 lines (فارسی \n English \n Melayu \n price)
            $lines = array_map('trim', explode("\n", $text));
            if (count($lines) < 4) {
                $bot->sendMessage(trans_user('format_error_item', $user), parse_mode: 'HTML');
                return;
            }

            $names = array_slice($lines, 0, 3);
            $price = (float) $lines[3];

            if ($price <= 0) {
                $bot->sendMessage(trans_user('price_error', $user));
                return;
            }

            $item->update([
                'name_fa' => $names[0],
                'name_en' => $names[1],
                'name_ms' => $names[2],
                'price' => $price,
            ]);

            $user->update(['state' => null]);
            $bot->sendMessage(trans_user('item_updated', $user, [
                'name' => $item->localizedName($user),
                'price' => number_format($price, 2),
            ]));
            return;
        }
    }

    // Unknown message - respond to registered users
    if ($user->is_registered) {
        $bot->sendMessage(
            text: trans_user('unknown_message', $user),
            reply_markup: mainMenuKeyboard($user),
        );
    }
});

/*
|--------------------------------------------------------------------------
| Helper Functions
|--------------------------------------------------------------------------
*/

function showCategories(TelegramBot $bot, ?User $user = null): void
{
    $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
    $keyboard = InlineKeyboardMarkup::make();

    foreach ($categories as $category) {
        $keyboard->addRow(
            InlineKeyboardButton::make(text: "🔵 {$category->localizedName($user)}", callback_data: "category:{$category->id}"),
        );
    }

    if (!$user) {
        $user = $bot->get('user');
    }
    $cart = Order::where('user_id', $user->id)->where('status', 'cart')->withCount('items')->first();

    if ($cart && $cart->items_count > 0) {
        $keyboard->addRow(
            InlineKeyboardButton::make(
                text: trans_user('cart_with_total', $user, ['total' => number_format($cart->total_price, 2)]),
                callback_data: 'view_cart',
            ),
        );
    }

    $bot->sendMessage(
        text: trans_user('menu_title', $user),
        reply_markup: $keyboard,
    );
}

function showAdminPanel(TelegramBot $bot, ?User $user = null): void
{
    if (!$user) {
        $user = $bot->get('user');
    }
    $bot->sendMessage(
        text: trans_user('admin_panel_title', $user),
        reply_markup: adminMenuKeyboard($user),
    );
}

function notifyAdmins(TelegramBot $bot, Order $order, User $customer): void
{
    $admins = User::where('is_admin', true)->get();

    if ($admins->isEmpty()) {
        return;
    }

    foreach ($admins as $admin) {
        $text = trans_user('new_order_notification', $admin);
        $text .= trans_user('order_number_admin', $admin, ['id' => $order->id]);
        $text .= trans_user('customer_name', $admin, ['name' => $customer->first_name]);
        $text .= trans_user('customer_phone', $admin, ['phone' => $customer->phone]);
        $text .= trans_user('customer_address', $admin, ['address' => $customer->address]);
        $text .= trans_user('items_label', $admin);

        foreach ($order->items as $item) {
            $subtotal = $item->price * $item->quantity;
            $text .= "• {$item->localizedItemName($admin)} × {$item->quantity} = " . number_format($subtotal, 2) . " RM\n";
        }

        $text .= trans_user('total_label', $admin, ['total' => number_format($order->total_price, 2)]);

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
                first_name: $customer->first_name ?? trans_user('customer_fallback', $admin),
                chat_id: $admin->telegram_id,
            );
        } catch (\Throwable $e) {
            \Log::error("Failed to notify admin {$admin->telegram_id}: " . $e->getMessage());
        }
    }
}
