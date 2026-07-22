<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Property;
use App\Models\Province;
use App\Models\Town;
use App\Models\User;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        $landlord = User::where('email', 'landlord@rentalapp.com')->first();

        if (! $landlord) {
            $this->command->warn('landlord@rentalapp.com not found — run TestUserSeeder first.');
            return;
        }

        // Use Lusaka Province → Lusaka District → Avondale town
        $province = Province::where('name', 'like', '%Lusaka%')->first();
        $district = District::where('name', 'Lusaka')->first();
        $town     = Town::where('district_id', $district?->id)->first();

        if (! $province || ! $district || ! $town) {
            $this->command->warn('Zambia location data missing — run ZambiaLocationSeeder first.');
            return;
        }

        $shared = [
            'landlord_id'        => $landlord->id,
            'province_id'        => $province->id,
            'district_id'        => $district->id,
            'town_id'            => $town->id,
            'latitude'           => (float) $town->latitude,
            'longitude'          => (float) $town->longitude,
            'approval_status'    => 'approved',
            'is_visible_in_search' => true,
            'is_auto_suspended'  => false,
            'bedrooms'           => 0,
            'bathrooms'          => 0,
            'location'           => null,
        ];

        $properties = [
            [
                'title'            => '3-Bedroom Family Home in Kabulonga',
                'description'      => 'Spacious family home with a large garden, borehole, and 24-hour security. Fully tiled throughout with modern kitchen and en-suite master bedroom.',
                'price'            => 8500,
                'property_type'    => 'residential',
                'property_subtype' => 'house',
                'listing_type'     => 'rent',
                'street_address'   => 'Plot 14, Kabulonga Road',
                'bedrooms'         => 3,
                'bathrooms'        => 2,
                'latitude'         => -15.3980,
                'longitude'        => 28.3420,
            ],
            [
                'title'            => 'Modern 2-Bedroom Apartment in Rhodespark',
                'description'      => 'Fully furnished apartment on the 3rd floor with lift access. DSTV, fibre internet, covered parking, and 24/7 security. Bills included.',
                'price'            => 5500,
                'property_type'    => 'residential',
                'property_subtype' => 'apartment',
                'listing_type'     => 'rent',
                'street_address'   => 'Flat 3B, Independence Avenue',
                'bedrooms'         => 2,
                'bathrooms'        => 2,
                'latitude'         => -15.4190,
                'longitude'        => 28.2930,
            ],
            [
                'title'            => 'Studio Room in Woodlands',
                'description'      => 'Self-contained bachelor room with private bathroom, kitchen corner, and prepaid electricity. Secure complex with borehole water.',
                'price'            => 1800,
                'property_type'    => 'residential',
                'property_subtype' => 'room',
                'listing_type'     => 'rent',
                'street_address'   => 'Flat C, Woodlands Road Extension',
                'bedrooms'         => 1,
                'bathrooms'        => 1,
                'latitude'         => -15.3710,
                'longitude'        => 28.3050,
            ],
            [
                'title'            => '4-Bedroom House for Sale in Ibex Hill',
                'description'      => 'Executive property on a 1200 sqm stand. Double garage, swimming pool, staff quarters, solar system, and borehole. Title deed ready.',
                'price'            => 950000,
                'property_type'    => 'residential',
                'property_subtype' => 'house',
                'listing_type'     => 'sale',
                'street_address'   => 'Stand 52, Ibex Hill Estate',
                'bedrooms'         => 4,
                'bathrooms'        => 3,
                'latitude'         => -15.4490,
                'longitude'        => 28.3710,
            ],
            [
                'title'            => 'Prime Office Space in Cairo Road CBD',
                'description'      => 'Open-plan office on the 5th floor with panoramic city views. Includes reception area, boardroom, fibre internet, and backup generator.',
                'price'            => 18000,
                'property_type'    => 'commercial',
                'property_subtype' => 'office_space',
                'listing_type'     => 'rent',
                'street_address'   => 'Cairo Road, City Centre',
                'latitude'         => -15.4167,
                'longitude'        => 28.2833,
            ],
            [
                'title'            => 'Retail Shop in Manda Hill Area',
                'description'      => 'Ground-floor retail space with high foot traffic near Manda Hill Mall. Ideal for a restaurant, boutique, or pharmacy. Includes back storeroom.',
                'price'            => 12000,
                'property_type'    => 'commercial',
                'property_subtype' => 'shop',
                'listing_type'     => 'rent',
                'street_address'   => '12 Great East Road',
                'latitude'         => -15.3970,
                'longitude'        => 28.3360,
            ],
            [
                'title'            => 'Warehouse for Rent in Light Industrial Area',
                'description'      => 'Secure 600sqm warehouse with roller doors, 3-phase power, office annex, and 24-hour fenced security. Truck access available.',
                'price'            => 22000,
                'property_type'    => 'commercial',
                'property_subtype' => 'warehouse',
                'listing_type'     => 'rent',
                'street_address'   => 'Plot 7, Kafue Road Industrial Area',
                'latitude'         => -15.4550,
                'longitude'        => 28.2600,
            ],
            [
                'title'            => '2-Bedroom Apartment for Sale in Longacres',
                'description'      => 'Well-maintained apartment in a secure complex. Open-plan living, modern kitchen, parking bay, and communal pool. Bond-ready.',
                'price'            => 320000,
                'property_type'    => 'residential',
                'property_subtype' => 'apartment',
                'listing_type'     => 'sale',
                'street_address'   => 'Haile Selassie Avenue, Longacres',
                'bedrooms'         => 2,
                'bathrooms'        => 1,
                'latitude'         => -15.4080,
                'longitude'        => 28.3010,
            ],
        ];

        foreach ($properties as $data) {
            Property::updateOrCreate(
                ['title' => $data['title'], 'landlord_id' => $landlord->id],
                array_merge($shared, $data),
            );
        }

        $this->command->info('Created ' . count($properties) . ' sample properties.');
    }
}
