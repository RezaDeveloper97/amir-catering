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

        foreach ($menu as $categoryData) {
            $category = Category::firstOrCreate(
                ['name_fa' => $categoryData['name_fa']],
                [
                    'name_en' => $categoryData['name_en'],
                    'name_ms' => $categoryData['name_ms'],
                    'sort_order' => $order++,
                ],
            );

            $itemOrder = 0;
            foreach ($categoryData['items'] as $item) {
                MenuItem::firstOrCreate(
                    ['category_id' => $category->id, 'name_fa' => $item['name_fa']],
                    [
                        'name_en' => $item['name_en'],
                        'name_ms' => $item['name_ms'],
                        'price' => $item['price'],
                        'sort_order' => $itemOrder++,
                    ],
                );
            }
        }
    }
}
