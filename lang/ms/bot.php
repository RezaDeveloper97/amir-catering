<?php

return [
    // Language selection
    'choose_language' => "🌐 زبان خود را انتخاب کنید:\nChoose your language:\nPilih bahasa anda:",
    'language_changed' => '✅ Bahasa ditukar kepada Bahasa Melayu.',

    // Menu buttons (ReplyKeyboard)
    'btn_order' => '🛒 Pesanan',
    'btn_change_address' => '📍 Tukar Alamat',
    'btn_my_orders' => '📋 Pesanan Saya',
    'btn_admin_panel' => '⚙️ Panel Admin',
    'btn_back_to_menu' => '🔴 Kembali ke Menu',
    'btn_manage_categories' => '📂 Urus Kategori',
    'btn_manage_items' => '🍽 Urus Item',
    'btn_send_location' => '📍 Hantar Lokasi',
    'btn_send_phone' => '📱 Hantar Nombor Telefon',
    'btn_language' => '🌐 Bahasa',

    // Start / Welcome
    'welcome_registered' => "Hai :name yang dihormati! 👋\n\nSelamat datang ke Amir Catering 🍽\n\nSila pilih dari menu di bawah:",
    'welcome_new' => "Hai :name yang dihormati! 👋\n\nSelamat datang ke Amir Catering 🍽\n\nAnda perlu mendaftar dahulu untuk membuat pesanan.",
    'start_description' => 'Mula',

    // Admin
    'admin_registered' => '✅ Anda telah didaftarkan sebagai admin. Mulai sekarang, semua pesanan akan dihantar kepada anda.',
    'no_admin_access' => '❌ Anda tidak mempunyai akses admin.',

    // General
    'please_register' => '❌ Sila daftar dahulu. Hantar arahan /start.',
    'showing_menu' => '🍽 Memuatkan menu...',
    'changing_address' => '📍 Menukar alamat...',
    'choose_from_menu' => 'Sila pilih dari menu di bawah:',
    'unknown_message' => '❌ Saya tidak faham! Sila gunakan menu di bawah:',
    'cancelled' => '❌ Dibatalkan.',

    // Categories & Items (customer)
    'menu_title' => "🍽 Menu Amir Catering\n\nPilih kategori:",
    'category_not_found' => '❌ Kategori tidak dijumpai',
    'select_item' => "📂 :name\n\nPilih item:",
    'item_not_found' => '❌ Item tidak dijumpai',
    'item_detail' => "🍽 :name\n💰 Harga: :price RM\n\nPilih kuantiti:",
    'btn_back' => '🔴 Kembali',

    // Cart
    'added_to_cart' => "✅ :name × :qty ditambah ke troli!\n\n💰 Jumlah troli: :total RM",
    'added_toast' => '✅ Ditambah!',
    'btn_continue_shopping' => '🔵 Terus Membeli',
    'btn_view_cart' => '🔵 Lihat Troli',
    'btn_place_order' => '🟢 Buat Pesanan',
    'cart_with_total' => '🔵 Troli (:total RM)',
    'cart_empty' => '🛒 Troli kosong',
    'cart_contents' => "🛒 Troli anda:\n\n",
    'cart_total' => "\n💰 Jumlah: :total RM",
    'btn_clear_cart' => '🔴 Kosongkan Troli',
    'cart_cleared' => '🗑 Troli dikosongkan.',
    'cart_cleared_toast' => '🗑 Troli dikosongkan',
    'item_removed_toast' => '🗑 Item dibuang',

    // Orders
    'no_orders' => '📋 Anda belum membuat sebarang pesanan.',
    'orders_header' => "📋 Pesanan terkini anda:\n\n",
    'order_line' => "🔖 Pesanan #:id - :date\n",
    'order_item_line' => "  • :name × :qty = :subtotal RM\n",
    'order_total' => "💰 Jumlah: :total RM\n",
    'order_status' => "📌 Status: :status\n\n",

    // Place order
    'order_placed' => "✅ Pesanan anda berjaya dibuat!\n\n",
    'order_number' => "🔖 Nombor pesanan: #:id\n\n",
    'order_preparing' => "\n\nPesanan anda sedang disediakan. Terima kasih! 🙏",
    'order_placed_toast' => '✅ Pesanan dibuat!',

    // Admin - Categories management
    'categories_header' => "📂 Kategori:\n\n✅ = Aktif | ❌ = Tidak Aktif",
    'btn_new_category' => '➕ Kategori Baharu',
    'category_detail' => "📂 :name\n\nStatus: :status\nBilangan item: :count",
    'status_active' => 'Aktif ✅',
    'status_inactive' => 'Tidak Aktif ❌',
    'btn_deactivate' => '❌ Nyahaktif',
    'btn_activate' => '✅ Aktifkan',
    'btn_view_items' => '🍽 Lihat Item',
    'btn_delete_category' => '🗑 Padam Kategori',
    'activated_toast' => '✅ Diaktifkan',
    'deactivated_toast' => '❌ Dinyahaktifkan',
    'deleted_toast' => '🗑 Dipadam',
    'not_found' => '❌ Tidak dijumpai',

    // Admin - Items management
    'items_select_category' => '🍽 Pilih kategori untuk melihat item:',
    'items_in_category' => '🍽 Item dalam :name:',
    'btn_new_item_in' => '➕ Item baharu dalam :name',
    'item_detail_admin' => "🍽 :name\n💰 Harga: :price RM\nKategori: :category\nStatus: :status",
    'btn_delete_item' => '🗑 Padam Item',

    // Admin - Add category/item
    'add_category_prompt' => "📂 Hantar nama kategori baharu dalam 3 bahasa (setiap satu dalam baris baharu):\n<b>Baris 1: فارسی\nBaris 2: English\nBaris 3: Melayu</b>\n\nContoh:\nنوشیدنی\nBeverages\nMinuman\n\n(Hantar /cancel untuk batal)",
    'add_item_prompt' => "🍽 Hantar nama item baharu dalam 3 bahasa dan harga (setiap satu dalam baris baharu):\n<b>Baris 1: فارسی\nBaris 2: English\nBaris 3: Melayu\nBaris 4: Harga</b>\n\nContoh:\nچلو کباب\nChelo Kabab\nChelo Kabab\n25.00\n\n(Hantar /cancel untuk batal)",
    'category_added' => "✅ Kategori \":name\" ditambah!\n\nUntuk pengurusan: /admin",
    'category_not_found_err' => '❌ Kategori tidak dijumpai.',
    'format_error_category' => "❌ Format salah. Sila hantar setiap bahasa dalam baris baharu:\n\nContoh:\nنوشیدنی\nBeverages\nMinuman",
    'format_error_item' => "❌ Format salah. Sila hantar setiap bahasa dan harga dalam baris baharu:\n\nContoh:\nچلو کباب\nChelo Kabab\nChelo Kabab\n25.00",
    'price_error' => '❌ Harga mestilah nombor positif.',
    'item_added' => "✅ \":name\" dengan harga :price RM ditambah ke :category!\n\nUntuk pengurusan: /admin",

    // Admin - Edit category/item
    'btn_edit_category' => '✏️ Edit Kategori',
    'btn_edit_item' => '✏️ Edit Item',
    'edit_category_prompt' => "✏️ Hantar nama baharu untuk \":name\" dalam 3 bahasa (setiap satu dalam baris baharu):\n<b>Baris 1: فارسی\nBaris 2: English\nBaris 3: Melayu</b>\n\n(Hantar /cancel untuk batal)",
    'edit_item_prompt' => "✏️ Hantar nama dan harga baharu untuk \":name\" (setiap satu dalam baris baharu):\n<b>Baris 1: فارسی\nBaris 2: English\nBaris 3: Melayu\nBaris 4: Harga</b>\n\n(Hantar /cancel untuk batal)",
    'category_updated' => '✅ Kategori ":name" dikemaskini!',
    'item_updated' => '✅ ":name" dikemaskini ke :price RM!',

    // Admin panel
    'admin_panel_title' => "⚙️ Panel Admin\n\nPilih bahagian:",

    // Admin notifications
    'new_order_notification' => "🔔 Pesanan Baharu!\n\n",
    'order_number_admin' => "🔖 Nombor pesanan: #:id\n",
    'customer_name' => "👤 Nama: :name\n",
    'customer_phone' => "📱 Telefon: :phone\n",
    'customer_address' => "📍 Alamat: :address\n\n",
    'items_label' => "📋 Item:\n",
    'total_label' => "\n💰 Jumlah: :total RM",
    'customer_fallback' => 'Pelanggan',

    // Registration conversation
    'reg_enter_address' => '📍 Sila masukkan alamat anda:',
    'reg_address_text_only' => '❌ Sila masukkan alamat anda dalam bentuk teks:',
    'reg_send_location' => '📍 Sekarang sila hantar lokasi anda:',
    'reg_use_location_button' => '❌ Sila gunakan butang di bawah untuk menghantar lokasi:',
    'reg_send_phone' => '📱 Sila hantar nombor telefon anda:',
    'reg_use_phone_button' => '❌ Sila gunakan butang di bawah untuk menghantar nombor telefon:',
    'reg_complete' => "✅ Pendaftaran berjaya diselesaikan!\n\nSelamat datang ke Amir Catering 🎉",

    // Change address conversation
    'addr_enter_new' => '📍 Sila masukkan alamat baharu anda:',
    'addr_text_only' => '❌ Sila masukkan alamat anda dalam bentuk teks:',
    'addr_send_location' => '📍 Sekarang sila hantar lokasi baharu anda:',
    'addr_use_location_button' => '❌ Sila gunakan butang di bawah untuk menghantar lokasi:',
    'addr_success' => '✅ Alamat anda berjaya ditukar!',
];
