<?php

namespace Database\Seeders;

use App\Models\TradeCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TradeCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Electrical',          'icon' => '⚡', 'sort_order' => 1],
            ['name' => 'Plumbing',             'icon' => '🔧', 'sort_order' => 2],
            ['name' => 'Building & Masonry',   'icon' => '🧱', 'sort_order' => 3],
            ['name' => 'Painting',             'icon' => '🖌️', 'sort_order' => 4],
            ['name' => 'Welding & Fabrication','icon' => '🔩', 'sort_order' => 5],
            ['name' => 'Carpentry',            'icon' => '🪚', 'sort_order' => 6],
            ['name' => 'Tiling & Flooring',   'icon' => '🏠', 'sort_order' => 7],
            ['name' => 'Roofing',              'icon' => '🏗️', 'sort_order' => 8],
            ['name' => 'Landscaping & Garden', 'icon' => '🌿', 'sort_order' => 9],
            ['name' => 'Cleaning Services',    'icon' => '🧹', 'sort_order' => 10],
            ['name' => 'Pest Control',         'icon' => '🐛', 'sort_order' => 11],
            ['name' => 'Security Services',    'icon' => '🔒', 'sort_order' => 12],
            ['name' => 'Solar Installation',   'icon' => '☀️', 'sort_order' => 13],
            ['name' => 'HVAC & Air Con',       'icon' => '❄️', 'sort_order' => 14],
            ['name' => 'Domestic Work',        'icon' => '🏡', 'sort_order' => 15],
        ];

        foreach ($categories as $cat) {
            TradeCategory::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                array_merge($cat, ['is_active' => true])
            );
        }
    }
}
