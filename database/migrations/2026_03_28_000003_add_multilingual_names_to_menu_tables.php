<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Categories: add multilingual name columns
        Schema::table('categories', function (Blueprint $table) {
            $table->string('name_fa')->nullable()->after('id');
            $table->string('name_en')->nullable()->after('name_fa');
            $table->string('name_ms')->nullable()->after('name_en');
        });

        // Copy existing name to name_fa
        DB::table('categories')->update([
            'name_fa' => DB::raw('`name`'),
        ]);

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        // Menu items: add multilingual name columns
        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('name_fa')->nullable()->after('category_id');
            $table->string('name_en')->nullable()->after('name_fa');
            $table->string('name_ms')->nullable()->after('name_en');
        });

        DB::table('menu_items')->update([
            'name_fa' => DB::raw('`name`'),
        ]);

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        // Order items: add multilingual columns
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('item_name_fa')->nullable()->after('order_id');
            $table->string('item_name_en')->nullable()->after('item_name_fa');
            $table->string('item_name_ms')->nullable()->after('item_name_en');
            $table->string('category_fa')->nullable()->after('item_name_ms');
            $table->string('category_en')->nullable()->after('category_fa');
            $table->string('category_ms')->nullable()->after('category_en');
        });

        DB::table('order_items')->update([
            'item_name_fa' => DB::raw('`item_name`'),
            'category_fa' => DB::raw('`category`'),
        ]);

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['item_name', 'category']);
        });
    }

    public function down(): void
    {
        // Restore categories
        Schema::table('categories', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
        });
        DB::table('categories')->update(['name' => DB::raw('`name_fa`')]);
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['name_fa', 'name_en', 'name_ms']);
        });

        // Restore menu_items
        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('name')->nullable()->after('category_id');
        });
        DB::table('menu_items')->update(['name' => DB::raw('`name_fa`')]);
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn(['name_fa', 'name_en', 'name_ms']);
        });

        // Restore order_items
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('item_name')->nullable()->after('order_id');
            $table->string('category')->nullable()->after('item_name');
        });
        DB::table('order_items')->update([
            'item_name' => DB::raw('`item_name_fa`'),
            'category' => DB::raw('`category_fa`'),
        ]);
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['item_name_fa', 'item_name_en', 'item_name_ms', 'category_fa', 'category_en', 'category_ms']);
        });
    }
};
