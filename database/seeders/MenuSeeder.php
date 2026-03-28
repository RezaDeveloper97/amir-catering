<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menu = [
            [
                'name_fa' => 'غذای اصلی',
                'name_en' => 'Main Dishes',
                'name_ms' => 'Hidangan Utama',
                'items' => [
                    ['name_fa' => 'خورشت قرمه', 'name_en' => 'Ghormeh Sabzi Stew', 'name_ms' => 'Stew Ghormeh Sabzi', 'price' => 20.00],
                    ['name_fa' => 'خورشت قیمه', 'name_en' => 'Gheymeh Stew', 'name_ms' => 'Stew Gheymeh', 'price' => 20.00],
                    ['name_fa' => 'زرشک پلو', 'name_en' => 'Zereshk Polo', 'name_ms' => 'Zereshk Polo', 'price' => 20.00],
                    ['name_fa' => 'مندی مرغ', 'name_en' => 'Chicken Mandi', 'name_ms' => 'Mandi Ayam', 'price' => 20.00],
                    ['name_fa' => 'مندی گوشت', 'name_en' => 'Meat Mandi', 'name_ms' => 'Mandi Daging', 'price' => 30.00],
                    ['name_fa' => 'باقالی پلو با گوشت', 'name_en' => 'Baghali Polo with Meat', 'name_ms' => 'Baghali Polo dengan Daging', 'price' => 30.00],
                    ['name_fa' => 'سبزی پلو با ماهی', 'name_en' => 'Sabzi Polo with Fish', 'name_ms' => 'Sabzi Polo dengan Ikan', 'price' => 28.00],
                    ['name_fa' => 'میرزا قاسمی', 'name_en' => 'Mirza Ghasemi', 'name_ms' => 'Mirza Ghasemi', 'price' => 18.00],
                    ['name_fa' => 'کشک بادمجان', 'name_en' => 'Kashk-e Bademjan', 'name_ms' => 'Kashk-e Bademjan', 'price' => 18.00],
                    ['name_fa' => 'کله پاچه', 'name_en' => 'Kaleh Pacheh', 'name_ms' => 'Kaleh Pacheh', 'price' => 120.00],
                ],
            ],
            [
                'name_fa' => 'کباب',
                'name_en' => 'Kebab',
                'name_ms' => 'Kebab',
                'items' => [
                    ['name_fa' => 'کباب بره (تک سیخ)', 'name_en' => 'Lamb Kebab (Single Skewer)', 'name_ms' => 'Kebab Kambing (Satu Cucuk)', 'price' => 18.00],
                    ['name_fa' => 'کباب بره (معمولی)', 'name_en' => 'Lamb Kebab (Regular)', 'name_ms' => 'Kebab Kambing (Biasa)', 'price' => 24.00],
                    ['name_fa' => 'چنجه کباب', 'name_en' => 'Chenjeh Kebab', 'name_ms' => 'Kebab Chenjeh', 'price' => 28.00],
                    ['name_fa' => 'جوجه کباب', 'name_en' => 'Joojeh Kebab', 'name_ms' => 'Kebab Joojeh', 'price' => 22.00],
                    ['name_fa' => 'وزیری کباب (میکس)', 'name_en' => 'Vaziri Kebab (Mix)', 'name_ms' => 'Kebab Vaziri (Campuran)', 'price' => 24.00],
                ],
            ],
            [
                'name_fa' => 'ساندویچ',
                'name_en' => 'Sandwich',
                'name_ms' => 'Sandwic',
                'items' => [
                    ['name_fa' => 'ساندویچ زبان', 'name_en' => 'Tongue Sandwich', 'name_ms' => 'Sandwic Lidah', 'price' => 22.00],
                    ['name_fa' => 'سالامی مرغ', 'name_en' => 'Chicken Salami', 'name_ms' => 'Salami Ayam', 'price' => 20.00],
                    ['name_fa' => 'سالامی گوشت', 'name_en' => 'Beef Salami', 'name_ms' => 'Salami Daging', 'price' => 20.00],
                    ['name_fa' => 'ساندویچ مرغ', 'name_en' => 'Chicken Sandwich', 'name_ms' => 'Sandwic Ayam', 'price' => 20.00],
                    ['name_fa' => 'ساندویچ کوبیده', 'name_en' => 'Koobideh Sandwich', 'name_ms' => 'Sandwic Koobideh', 'price' => 20.00],
                    ['name_fa' => 'کباب ترکی', 'name_en' => 'Turkish Kebab', 'name_ms' => 'Kebab Turki', 'price' => 20.00],
                    ['name_fa' => 'هات داگ', 'name_en' => 'Hot Dog', 'name_ms' => 'Hot Dog', 'price' => 20.00],
                    ['name_fa' => 'چیز برگر', 'name_en' => 'Cheeseburger', 'name_ms' => 'Burger Keju', 'price' => 20.00],
                    ['name_fa' => 'بندری', 'name_en' => 'Bandari', 'name_ms' => 'Bandari', 'price' => 20.00],
                ],
            ],
            [
                'name_fa' => 'پیتزا',
                'name_en' => 'Pizza',
                'name_ms' => 'Pizza',
                'items' => [
                    ['name_fa' => 'پیتزا مخصوص', 'name_en' => 'Special Pizza', 'name_ms' => 'Pizza Istimewa', 'price' => 38.00],
                    ['name_fa' => 'پیتزا میکس', 'name_en' => 'Mix Pizza', 'name_ms' => 'Pizza Campuran', 'price' => 25.00],
                ],
            ],
            [
                'name_fa' => 'پیش غذا',
                'name_en' => 'Appetizers',
                'name_ms' => 'Pembuka Selera',
                'items' => [
                    ['name_fa' => 'سالاد شیرازی', 'name_en' => 'Shirazi Salad', 'name_ms' => 'Salad Shirazi', 'price' => 7.00],
                    ['name_fa' => 'سالاد فصل', 'name_en' => 'Season Salad', 'name_ms' => 'Salad Musim', 'price' => 7.00],
                    ['name_fa' => 'ماست', 'name_en' => 'Yogurt', 'name_ms' => 'Yogurt', 'price' => 7.00],
                ],
            ],
            [
                'name_fa' => 'نوشیدنی',
                'name_en' => 'Beverages',
                'name_ms' => 'Minuman',
                'items' => [
                    ['name_fa' => 'دوغ', 'name_en' => 'Doogh', 'name_ms' => 'Doogh', 'price' => 3.50],
                    ['name_fa' => 'نوشابه', 'name_en' => 'Soft Drink', 'name_ms' => 'Minuman Ringan', 'price' => 3.50],
                ],
            ],
        ];

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
