<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VerificationTierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiers = [
            [
                'name' => 'basic',
                'display_name' => 'Basic (Free)',
                'price_display' => 'Free',
                'price_amount' => 0,
                'property_limit' => 5,
                'features' => [
                    'Up to 5 Properties',
                    'Basic Support',
                    'Standard Listings',
                    'Manual Verification'
                ],
                'styling' => [
                    'color' => 'border-gray-200 hover:border-brand-blue',
                    'bg' => 'bg-white dark:bg-gray-800',
                    'checkColor' => 'text-gray-400'
                ]
            ],
            [
                'name' => 'trusted',
                'display_name' => 'Trusted Landlord',
                'price_display' => 'Verified',
                'price_amount' => 0,
                'property_limit' => -1, // Unlimited
                'features' => [
                    'Unlimited Properties',
                    'Verified Badge',
                    'Priority Reviews',
                    'Common Listings',
                    'Auto-Approved Listings'
                ],
                'styling' => [
                    'color' => 'border-brand-blue ring-1 ring-brand-blue',
                    'bg' => 'bg-blue-50 dark:bg-blue-900/10',
                    'checkColor' => 'text-brand-blue'
                ]
            ],
            [
                'name' => 'premium',
                'display_name' => 'Premium Landlord',
                'price_display' => 'K300/mo',
                'price_amount' => 300,
                'property_limit' => -1, // Unlimited
                'features' => [
                    'Unlimited Properties',
                    'Premium Badge',
                    'Top Placement',
                    'Video Tours',
                    'Advanced Analytics'
                ],
                'styling' => [
                    'color' => 'border-brand-gold ring-1 ring-brand-gold',
                    'bg' => 'bg-yellow-50 dark:bg-yellow-900/10',
                    'checkColor' => 'text-brand-gold'
                ]
            ]
        ];

        foreach ($tiers as $tier) {
            \App\Models\VerificationTier::updateOrCreate(
                ['name' => $tier['name']],
                $tier
            );
        }
    }
}
