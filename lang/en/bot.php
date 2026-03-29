<?php

return [
    // Language selection
    'choose_language' => "🌐 زبان خود را انتخاب کنید:\nChoose your language:\nPilih bahasa anda:",
    'language_changed' => '✅ Language changed to English.',

    // Menu buttons (ReplyKeyboard)
    'btn_order' => '🛒 Order',
    'btn_change_address' => '📍 Change Address',
    'btn_my_orders' => '📋 My Orders',
    'btn_admin_panel' => '⚙️ Admin Panel',
    'btn_back_to_menu' => '🔴 Back to Menu',
    'btn_manage_categories' => '📂 Manage Categories',
    'btn_manage_items' => '🍽 Manage Items',
    'btn_send_location' => '📍 Send Location',
    'btn_send_phone' => '📱 Send Phone Number',
    'btn_language' => '🌐 Language',

    // Start / Welcome
    'welcome_registered' => "Hello dear :name! 👋\n\nWelcome to Amir Catering 🍽\n\nChoose from the menu below:",
    'welcome_new' => "Hello dear :name! 👋\n\nWelcome to Amir Catering 🍽\n\nYou need to register first to place an order.",
    'start_description' => 'Start',

    // Admin
    'admin_registered' => '✅ You have been registered as an admin. From now on, all orders will be sent to you.',
    'no_admin_access' => '❌ You do not have admin access.',

    // General
    'please_register' => '❌ Please register first. Send /start command.',
    'showing_menu' => '🍽 Loading menu...',
    'changing_address' => '📍 Changing address...',
    'choose_from_menu' => 'Choose from the menu below:',
    'unknown_message' => '❌ I did not understand! Please use the menu below:',
    'cancelled' => '❌ Cancelled.',

    // Categories & Items (customer)
    'menu_title' => "🍽 Amir Catering Menu\n\nSelect a category:",
    'category_not_found' => '❌ Category not found',
    'select_item' => "📂 :name\n\nSelect an item:",
    'item_not_found' => '❌ Item not found',
    'item_detail' => "🍽 :name\n💰 Price: :price RM\n\nSelect quantity:",
    'btn_back' => '🔴 Back',

    // Cart
    'added_to_cart' => "✅ :name × :qty added to cart!\n\n💰 Cart total: :total RM",
    'added_toast' => '✅ Added!',
    'btn_continue_shopping' => '🔵 Continue Shopping',
    'btn_view_cart' => '🔵 View Cart',
    'btn_place_order' => '🟢 Place Order',
    'cart_with_total' => '🔵 Cart (:total RM)',
    'cart_empty' => '🛒 Cart is empty',
    'cart_contents' => "🛒 Your cart:\n\n",
    'cart_total' => "\n💰 Total: :total RM",
    'btn_clear_cart' => '🔴 Clear Cart',
    'cart_cleared' => '🗑 Cart cleared.',
    'cart_cleared_toast' => '🗑 Cart cleared',
    'item_removed_toast' => '🗑 Item removed',

    // Orders
    'no_orders' => '📋 You have not placed any orders yet.',
    'orders_header' => "📋 Your recent orders:\n\n",
    'order_line' => "🔖 Order #:id - :date\n",
    'order_item_line' => "  • :name × :qty = :subtotal RM\n",
    'order_total' => "💰 Total: :total RM\n",
    'order_status' => "📌 Status: :status\n\n",

    // Place order
    'order_placed' => "✅ Your order has been placed successfully!\n\n",
    'order_number' => "🔖 Order number: #:id\n\n",
    'order_preparing' => "\n\nYour order is being prepared. Thank you! 🙏",
    'order_placed_toast' => '✅ Order placed!',

    // Admin - Categories management
    'categories_header' => "📂 Categories:\n\n✅ = Active | ❌ = Inactive",
    'btn_new_category' => '➕ New Category',
    'category_detail' => "📂 :name\n\nStatus: :status\nItem count: :count",
    'status_active' => 'Active ✅',
    'status_inactive' => 'Inactive ❌',
    'btn_deactivate' => '❌ Deactivate',
    'btn_activate' => '✅ Activate',
    'btn_view_items' => '🍽 View Items',
    'btn_delete_category' => '🗑 Delete Category',
    'activated_toast' => '✅ Activated',
    'deactivated_toast' => '❌ Deactivated',
    'deleted_toast' => '🗑 Deleted',
    'not_found' => '❌ Not found',

    // Admin - Items management
    'items_select_category' => '🍽 Select a category to view its items:',
    'items_in_category' => '🍽 Items in :name:',
    'btn_new_item_in' => '➕ New item in :name',
    'item_detail_admin' => "🍽 :name\n💰 Price: :price RM\nCategory: :category\nStatus: :status",
    'btn_delete_item' => '🗑 Delete Item',

    // Admin - Add category/item
    'add_category_prompt' => "📂 Send the new category name in 3 languages (each on a new line):\n<b>Line 1: فارسی\nLine 2: English\nLine 3: Melayu</b>\n\nExample:\nنوشیدنی\nBeverages\nMinuman\n\n(Send /cancel to cancel)",
    'add_item_prompt' => "🍽 Send the new item name in 3 languages and price (each on a new line):\n<b>Line 1: فارسی\nLine 2: English\nLine 3: Melayu\nLine 4: Price</b>\n\nExample:\nچلو کباب\nChelo Kabab\nChelo Kabab\n25.00\n\n(Send /cancel to cancel)",
    'category_added' => "✅ Category \":name\" added!\n\nFor management: /admin",
    'category_not_found_err' => '❌ Category not found.',
    'format_error_category' => "❌ Wrong format. Please send each language on a new line:\n\nExample:\nنوشیدنی\nBeverages\nMinuman",
    'format_error_item' => "❌ Wrong format. Please send each language and price on a new line:\n\nExample:\nچلو کباب\nChelo Kabab\nChelo Kabab\n25.00",
    'price_error' => '❌ Price must be a positive number.',
    'item_added' => "✅ \":name\" with price :price RM added to :category!\n\nFor management: /admin",

    // Admin - Edit category/item
    'btn_edit_category' => '✏️ Edit Category',
    'btn_edit_item' => '✏️ Edit Item',
    'edit_category_prompt' => "✏️ Send the new name for \":name\" in 3 languages (each on a new line):\n<b>Line 1: فارسی\nLine 2: English\nLine 3: Melayu</b>\n\n(Send /cancel to cancel)",
    'edit_item_prompt' => "✏️ Send the new name and price for \":name\" (each on a new line):\n<b>Line 1: فارسی\nLine 2: English\nLine 3: Melayu\nLine 4: Price</b>\n\n(Send /cancel to cancel)",
    'category_updated' => '✅ Category ":name" updated!',
    'item_updated' => '✅ ":name" updated to :price RM!',

    // Admin panel
    'admin_panel_title' => "⚙️ Admin Panel\n\nSelect a section:",

    // Admin notifications
    'new_order_notification' => "🔔 New Order!\n\n",
    'order_number_admin' => "🔖 Order number: #:id\n",
    'customer_name' => "👤 Name: :name\n",
    'customer_phone' => "📱 Phone: :phone\n",
    'customer_address' => "📍 Address: :address\n\n",
    'items_label' => "📋 Items:\n",
    'total_label' => "\n💰 Total: :total RM",
    'customer_fallback' => 'Customer',

    // Registration conversation
    'reg_enter_address' => '📍 Please enter your address:',
    'reg_address_text_only' => '❌ Please enter your address as text:',
    'reg_send_location' => '📍 Now please send your location:',
    'reg_use_location_button' => '❌ Please use the button below to send your location:',
    'reg_send_phone' => '📱 Please send your phone number:',
    'reg_use_phone_button' => '❌ Please use the button below to send your phone number:',
    'reg_complete' => "✅ Registration completed successfully!\n\nWelcome to Amir Catering 🎉",

    // Change address conversation
    'addr_enter_new' => '📍 Please enter your new address:',
    'addr_text_only' => '❌ Please enter your address as text:',
    'addr_send_location' => '📍 Now please send your new location:',
    'addr_use_location_button' => '❌ Please use the button below to send your location:',
    'addr_success' => '✅ Your address has been changed successfully!',
];
