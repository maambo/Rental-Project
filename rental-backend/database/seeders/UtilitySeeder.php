<?php

namespace Database\Seeders;

use App\Models\UtilityOption;
use App\Models\UtilityType;
use Illuminate\Database\Seeder;

class UtilitySeeder extends Seeder
{
    public function run(): void
    {
        $utilities = [
            [
                'name' => 'Power', 'icon' => 'bolt', 'sort_order' => 1,
                'options' => [
                    ['label' => 'ZESCO (Grid)',  'value' => 'zesco'],
                    ['label' => 'Solar',         'value' => 'solar'],
                    ['label' => 'Generator',     'value' => 'generator'],
                    ['label' => 'Solar + ZESCO', 'value' => 'solar_zesco'],
                    ['label' => 'None',          'value' => 'none'],
                ],
            ],
            [
                'name' => 'Water', 'icon' => 'water', 'sort_order' => 2,
                'options' => [
                    ['label' => 'Municipal (LWSC)',  'value' => 'municipal'],
                    ['label' => 'Borehole / Well',   'value' => 'borehole'],
                    ['label' => 'Water Tanker',      'value' => 'tanker'],
                    ['label' => 'Spring / River',    'value' => 'spring'],
                    ['label' => 'None',              'value' => 'none'],
                ],
            ],
            [
                'name' => 'Internet', 'icon' => 'wifi', 'sort_order' => 3,
                'options' => [
                    ['label' => 'Fibre',    'value' => 'fibre'],
                    ['label' => 'Airtel 4G','value' => 'airtel'],
                    ['label' => 'MTN 4G',   'value' => 'mtn'],
                    ['label' => 'Zamtel',   'value' => 'zamtel'],
                    ['label' => 'Starlink',  'value' => 'starlink'],
                    ['label' => 'None',     'value' => 'none'],
                ],
            ],
            [
                'name' => 'Sewage', 'icon' => 'pipes', 'sort_order' => 4,
                'options' => [
                    ['label' => 'Sewer (Council)', 'value' => 'sewer'],
                    ['label' => 'Septic Tank',     'value' => 'septic'],
                    ['label' => 'Pit Latrine',     'value' => 'pit_latrine'],
                    ['label' => 'None',            'value' => 'none'],
                ],
            ],
        ];

        foreach ($utilities as $sort => $data) {
            $type = UtilityType::create([
                'name'       => $data['name'],
                'icon'       => $data['icon'],
                'is_active'  => true,
                'sort_order' => $data['sort_order'],
            ]);

            foreach ($data['options'] as $i => $option) {
                UtilityOption::create([
                    'utility_type_id' => $type->id,
                    'label'           => $option['label'],
                    'value'           => $option['value'],
                    'sort_order'      => $i,
                ]);
            }
        }
    }
}
