<?php

namespace App\Console\Commands;

use App\Models\LandlordApplication;
use App\Models\Property;
use App\Models\PropertyApplication;
use App\Models\User;
use App\Models\UtilityType;
use Illuminate\Console\Command;

class CleanupFlowTestData extends Command
{
    protected $signature   = 'test:cleanup-flow';
    protected $description = 'Remove all @flowtest.com users and their associated data';

    public function handle(): int
    {
        $users = User::where('email', 'like', '%@flowtest.com')->get();

        foreach ($users as $user) {
            // Remove property applications submitted BY this user (as tenant)
            PropertyApplication::where('user_id', $user->id)->delete();

            // Remove properties owned by this user (as landlord)
            $properties = Property::where('landlord_id', $user->id)->get();
            foreach ($properties as $property) {
                // Pivot cleanup — utilities and applications for this property
                $property->utilities()->detach();
                PropertyApplication::where('property_id', $property->id)->delete();
                $property->images()->delete();
                $property->delete();
            }

            LandlordApplication::where('user_id', $user->id)->delete();
            $user->delete();
        }

        // Remove flow-test utility types
        UtilityType::where('name', 'like', 'FlowTest%')->each(function ($ut) {
            $ut->options()->delete();
            $ut->delete();
        });

        $this->info("Flow test data cleaned up ({$users->count()} user(s) removed).");

        return self::SUCCESS;
    }
}
