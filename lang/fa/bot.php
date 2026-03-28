<?php

return [
    // Language selection
    'choose_language' => "🌐 زبان خود را انتخاب کنید:\nChoose your language:\nPilih bahasa anda:",
    'language_changed' => '✅ زبان به فارسی تغییر کرد.',

    // Menu buttons (ReplyKeyboard)
    'btn_order' => '🛒 سفارش',
    'btn_change_address' => '📍 تغییر آدرس',
    'btn_my_orders' => '📋 سفارشات من',
    'btn_admin_panel' => '⚙️ پنل مدیریت',
    'btn_back_to_menu' => '🔴 بازگشت به منو',
    'btn_manage_categories' => '📂 مدیریت دسته‌بندی‌ها',
    'btn_manage_items' => '🍽 مدیریت آیتم‌ها',
    'btn_send_location' => '📍 ارسال لوکیشن',
    'btn_send_phone' => '📱 ارسال شماره موبایل',
    'btn_language' => '🌐 زبان',

    // Start / Welcome
    'welcome_registered' => "سلام :name عزیز! 👋\n\nبه امیر کترینگ خوش آمدید 🍽\n\nاز منوی زیر انتخاب کنید:",
    'welcome_new' => "سلام :name عزیز! 👋\n\nبه امیر کترینگ خوش آمدید 🍽\n\nبرای ثبت سفارش ابتدا باید ثبت نام کنید.",
    'start_description' => 'شروع',

    // Admin
    'admin_registered' => '✅ شما به عنوان ادمین ثبت شدید. از این پس تمام سفارشات برای شما ارسال خواهد شد.',
    'no_admin_access' => '❌ شما دسترسی ادمین ندارید.',

    // General
    'please_register' => '❌ لطفاً ابتدا ثبت نام کنید. دستور /start را بزنید.',
    'showing_menu' => '🍽 در حال نمایش منو...',
    'changing_address' => '📍 در حال تغییر آدرس...',
    'choose_from_menu' => 'از منوی زیر انتخاب کنید:',
    'unknown_message' => '❌ متوجه نشدم! لطفاً از منوی زیر استفاده کنید:',
    'cancelled' => '❌ لغو شد.',

    // Categories & Items (customer)
    'menu_title' => "🍽 منوی امیر کترینگ\n\nیک دسته‌بندی انتخاب کنید:",
    'category_not_found' => '❌ دسته‌بندی یافت نشد',
    'select_item' => "📂 :name\n\nیک آیتم انتخاب کنید:",
    'item_not_found' => '❌ آیتم یافت نشد',
    'item_detail' => "🍽 :name\n💰 قیمت: :price RM\n\nتعداد را انتخاب کنید:",
    'btn_back' => '🔴 بازگشت',

    // Cart
    'added_to_cart' => "✅ :name × :qty به سبد خرید اضافه شد!\n\n💰 جمع سبد خرید: :total RM",
    'added_toast' => '✅ اضافه شد!',
    'btn_continue_shopping' => '🔵 ادامه خرید',
    'btn_view_cart' => '🔵 مشاهده سبد خرید',
    'btn_place_order' => '🟢 ثبت سفارش',
    'cart_with_total' => '🔵 سبد خرید (:total RM)',
    'cart_empty' => '🛒 سبد خرید خالی است',
    'cart_contents' => "🛒 سبد خرید شما:\n\n",
    'cart_total' => "\n💰 جمع کل: :total RM",
    'btn_clear_cart' => '🔴 خالی کردن سبد',
    'cart_cleared' => '🗑 سبد خرید خالی شد.',
    'cart_cleared_toast' => '🗑 سبد خالی شد',
    'item_removed_toast' => '🗑 آیتم حذف شد',

    // Orders
    'no_orders' => '📋 شما هنوز سفارشی ثبت نکرده‌اید.',
    'orders_header' => "📋 آخرین سفارشات شما:\n\n",
    'order_line' => "🔖 سفارش #:id - :date\n",
    'order_item_line' => "  • :name × :qty = :subtotal RM\n",
    'order_total' => "💰 جمع: :total RM\n",
    'order_status' => "📌 وضعیت: :status\n\n",

    // Place order
    'order_placed' => "✅ سفارش شما با موفقیت ثبت شد!\n\n",
    'order_number' => "🔖 شماره سفارش: #:id\n\n",
    'order_preparing' => "\n\nسفارش شما در حال آماده‌سازی است. با تشکر از شما! 🙏",
    'order_placed_toast' => '✅ سفارش ثبت شد!',

    // Admin - Categories management
    'categories_header' => "📂 دسته‌بندی‌ها:\n\n✅ = فعال | ❌ = غیرفعال",
    'btn_new_category' => '➕ دسته‌بندی جدید',
    'category_detail' => "📂 :name\n\nوضعیت: :status\nتعداد آیتم: :count",
    'status_active' => 'فعال ✅',
    'status_inactive' => 'غیرفعال ❌',
    'btn_deactivate' => '❌ غیرفعال کردن',
    'btn_activate' => '✅ فعال کردن',
    'btn_view_items' => '🍽 مشاهده آیتم‌ها',
    'btn_delete_category' => '🗑 حذف دسته‌بندی',
    'activated_toast' => '✅ فعال شد',
    'deactivated_toast' => '❌ غیرفعال شد',
    'deleted_toast' => '🗑 حذف شد',
    'not_found' => '❌ یافت نشد',

    // Admin - Items management
    'items_select_category' => '🍽 یک دسته‌بندی انتخاب کنید تا آیتم‌هایش را ببینید:',
    'items_in_category' => '🍽 آیتم‌های :name:',
    'btn_new_item_in' => '➕ آیتم جدید در :name',
    'item_detail_admin' => "🍽 :name\n💰 قیمت: :price RM\nدسته: :category\nوضعیت: :status",
    'btn_delete_item' => '🗑 حذف آیتم',

    // Admin - Add category/item
    'add_category_prompt' => "📂 نام دسته‌بندی جدید را به ۳ زبان بفرستید:\n<b>فارسی | English | Melayu</b>\n\nمثال: نوشیدنی | Beverages | Minuman\n\n(برای لغو /cancel بزنید)",
    'add_item_prompt' => "🍽 نام آیتم جدید را به ۳ زبان و قیمت بفرستید:\n<b>فارسی | English | Melayu - قیمت</b>\n\nمثال: چلو کباب | Chelo Kabab | Chelo Kabab - 25.00\n\n(برای لغو /cancel بزنید)",
    'category_added' => "✅ دسته‌بندی «:name» اضافه شد!\n\nبرای مدیریت: /admin",
    'category_not_found_err' => '❌ دسته‌بندی یافت نشد.',
    'format_error_category' => "❌ فرمت اشتباه. لطفاً به فرمت زیر بفرستید:\nفارسی | English | Melayu\n\nمثال: نوشیدنی | Beverages | Minuman",
    'format_error_item' => "❌ فرمت اشتباه. لطفاً به فرمت زیر بفرستید:\nفارسی | English | Melayu - قیمت\n\nمثال: چلو کباب | Chelo Kabab | Chelo Kabab - 25.00",
    'price_error' => '❌ قیمت باید عدد مثبت باشد.',
    'item_added' => "✅ «:name» با قیمت :price RM به :category اضافه شد!\n\nبرای مدیریت: /admin",

    // Admin - Edit category/item
    'btn_edit_category' => '✏️ ویرایش دسته‌بندی',
    'btn_edit_item' => '✏️ ویرایش آیتم',
    'edit_category_prompt' => "✏️ نام جدید دسته‌بندی «:name» را به ۳ زبان بفرستید:\n<b>فارسی | English | Melayu</b>\n\n(برای لغو /cancel بزنید)",
    'edit_item_prompt' => "✏️ نام و قیمت جدید «:name» را بفرستید:\n<b>فارسی | English | Melayu - قیمت</b>\n\n(برای لغو /cancel بزنید)",
    'category_updated' => '✅ دسته‌بندی «:name» ویرایش شد!',
    'item_updated' => '✅ «:name» با قیمت :price RM ویرایش شد!',

    // Admin panel
    'admin_panel_title' => "⚙️ پنل مدیریت\n\nیک بخش انتخاب کنید:",

    // Admin notifications
    'new_order_notification' => "🔔 سفارش جدید!\n\n",
    'order_number_admin' => "🔖 شماره سفارش: #:id\n",
    'customer_name' => "👤 نام: :name\n",
    'customer_phone' => "📱 شماره تماس: :phone\n",
    'customer_address' => "📍 آدرس: :address\n\n",
    'items_label' => "📋 آیتم‌ها:\n",
    'total_label' => "\n💰 جمع کل: :total RM",
    'customer_fallback' => 'مشتری',

    // Registration conversation
    'reg_enter_address' => '📍 لطفاً آدرس خود را وارد کنید:',
    'reg_address_text_only' => '❌ لطفاً آدرس خود را به صورت متنی وارد کنید:',
    'reg_send_location' => '📍 حالا لطفاً لوکیشن خود را ارسال کنید:',
    'reg_use_location_button' => '❌ لطفاً از دکمه زیر برای ارسال لوکیشن استفاده کنید:',
    'reg_send_phone' => '📱 لطفاً شماره موبایل خود را ارسال کنید:',
    'reg_use_phone_button' => '❌ لطفاً از دکمه زیر برای ارسال شماره موبایل استفاده کنید:',
    'reg_complete' => "✅ ثبت نام شما با موفقیت تکمیل شد!\n\nبه امیر کترینگ خوش آمدید 🎉",

    // Change address conversation
    'addr_enter_new' => '📍 لطفاً آدرس جدید خود را وارد کنید:',
    'addr_text_only' => '❌ لطفاً آدرس خود را به صورت متنی وارد کنید:',
    'addr_send_location' => '📍 حالا لطفاً لوکیشن جدید خود را ارسال کنید:',
    'addr_use_location_button' => '❌ لطفاً از دکمه زیر برای ارسال لوکیشن استفاده کنید:',
    'addr_success' => '✅ آدرس شما با موفقیت تغییر کرد!',
];
