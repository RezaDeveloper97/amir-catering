<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menu = config('menu');
        $order = 0;

        foreach ($menu as $categoryName => $items) {
            $category = Category::firstOrCreate(
                ['name' => $categoryName],
                ['sort_order' => $order++],
            );

            $itemOrder = 0;
            foreach ($items as $item) {
                MenuItem::firstOrCreate(
                    ['category_id' => $category->id, 'name' => $item['name']],
                    ['price' => $item['price'], 'sort_order' => $itemOrder++],
                );
            }
        }
    }
}
