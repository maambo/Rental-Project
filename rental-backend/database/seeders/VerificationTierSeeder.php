<?php

namespace Database\Seeders;

use App\Models\VerificationTier;
use Illuminate\Database\Seeder;

class VerificationTierSeeder extends Seeder
{
    public function run(): void
    {
        $tiers = [
            // ── Landlord tiers ─────────────────────────────────────────────
            [
                'name'           => 'starter',
                'tier_type'      => 'landlord',
                'display_name'   => 'Starter',
                'price_display'  => 'Free',
                'price_amount'   => 0,
                'property_limit' => 1,
                'features'       => [
                    '1 property listing',
                    'Manual rent collection (proof upload)',
                    'Tenant application management',
                    'In-app messaging',
                    'Email support',
                ],
                'styling' => [
                    'color'      => 'border-gray-700',
                    'bg'         => 'bg-light-bg',
                    'checkColor' => 'text-gray-400',
                    'badge'      => 'bg-gray-500/20 text-gray-400',
                ],
            ],
            [
                'name'           => 'basic',
                'tier_type'      => 'landlord',
                'display_name'   => 'Basic',
                'price_display'  => 'K99/mo',
                'price_amount'   => 99,
                'property_limit' => 5,
                'features'       => [
                    'Up to 5 property listings',
                    'Lease agreement generation',
                    'Tour request management',
                    'Maintenance tracking',
                    'Billing records & history',
                    'Priority search ranking',
                    'Email support',
                ],
                'styling' => [
                    'color'      => 'border-blue-500/50',
                    'bg'         => 'bg-blue-500/5',
                    'checkColor' => 'text-blue-400',
                    'badge'      => 'bg-blue-500/20 text-blue-400',
                ],
            ],
            [
                'name'           => 'professional',
                'tier_type'      => 'landlord',
                'display_name'   => 'Professional',
                'price_display'  => 'K299/mo',
                'price_amount'   => 299,
                'property_limit' => 15,
                'features'       => [
                    'Up to 15 property listings',
                    'All Basic features',
                    'Bank & mobile money rent collection',
                    'Automated billing reminders',
                    'Financial reports & CSV export',
                    '2 featured listings per month',
                    'Verified landlord badge',
                    'Analytics dashboard',
                    'Priority support',
                ],
                'styling' => [
                    'color'      => 'border-brand-red/50',
                    'bg'         => 'bg-brand-red/5',
                    'checkColor' => 'text-brand-red',
                    'badge'      => 'bg-brand-red/20 text-red-400',
                ],
            ],
            [
                'name'           => 'enterprise',
                'tier_type'      => 'landlord',
                'display_name'   => 'Enterprise',
                'price_display'  => 'K699/mo',
                'price_amount'   => 699,
                'property_limit' => -1,
                'features'       => [
                    'Unlimited property listings',
                    'All Professional features',
                    'Bulk property management',
                    'API access',
                    'Dedicated account manager',
                    'Custom onboarding',
                    'White-glove support',
                ],
                'styling' => [
                    'color'      => 'border-yellow-500/50',
                    'bg'         => 'bg-yellow-500/5',
                    'checkColor' => 'text-yellow-400',
                    'badge'      => 'bg-yellow-500/20 text-yellow-400',
                ],
            ],

            // ── Tenant tiers ───────────────────────────────────────────────
            [
                'name'           => 'tenant_free',
                'tier_type'      => 'tenant',
                'display_name'   => 'Free',
                'price_display'  => 'Free',
                'price_amount'   => 0,
                'property_limit' => 0,
                'features'       => [
                    'Browse & search properties',
                    'Submit rental applications',
                    'In-app messaging',
                    'View rental history',
                ],
                'styling' => [
                    'color'      => 'border-gray-700',
                    'bg'         => 'bg-light-bg',
                    'checkColor' => 'text-gray-400',
                    'badge'      => 'bg-gray-500/20 text-gray-400',
                ],
            ],
            [
                'name'           => 'tenant_plus',
                'tier_type'      => 'tenant',
                'display_name'   => 'Tenant Plus',
                'price_display'  => 'K49/mo',
                'price_amount'   => 49,
                'property_limit' => 0,
                'features'       => [
                    'All Free features',
                    'Saved search alerts (email/SMS)',
                    'Priority application badge',
                    'Pre-filled application templates',
                    'Reference letter download',
                ],
                'styling' => [
                    'color'      => 'border-green-500/50',
                    'bg'         => 'bg-green-500/5',
                    'checkColor' => 'text-green-400',
                    'badge'      => 'bg-green-500/20 text-green-400',
                ],
            ],
        ];

        foreach ($tiers as $tier) {
            VerificationTier::updateOrCreate(
                ['name' => $tier['name']],
                $tier
            );
        }

        // Remove old tier names that are no longer used
        VerificationTier::whereIn('name', ['trusted', 'premium'])->delete();
    }
}
