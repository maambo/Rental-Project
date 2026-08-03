<?php

namespace App\Services;

use App\Models\Property;
use App\Models\TradeCategory;
use App\Models\UtilityOption;
use App\Models\UtilityType;
use App\Notifications\PropertyUtilityRetired;
use App\Notifications\TradeCategoryRetired;
use Illuminate\Support\Facades\Notification;

/**
 * Handles the fallout when an admin retires something from a shared catalog
 * (worker trade categories, property utility types/options): notify everyone
 * whose record now points at a retired entry so they can fix it.
 */
class CatalogRetirementService
{
    /**
     * Notify every worker whose profile uses this category. Their profile is
     * hidden from the marketplace by WorkerProfile::scopeBookable() for as long
     * as the category stays inactive.
     *
     * @return int number of workers notified
     */
    public function retireTradeCategory(TradeCategory $category): int
    {
        $users = $category->workerProfiles()
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter();

        if ($users->isEmpty()) {
            return 0;
        }

        Notification::send($users, new TradeCategoryRetired($category));

        return $users->count();
    }

    /**
     * Notify the landlord of every property that has this utility type attached.
     *
     * @return int number of properties affected
     */
    public function retireUtilityType(UtilityType $utility): int
    {
        $properties = Property::query()
            ->whereHas('utilities', fn ($q) => $q->where('utility_types.id', $utility->id))
            ->with('landlord')
            ->get();

        return $this->notifyLandlords($properties, $utility->name, null);
    }

    /**
     * Notify the landlord of every property that had specifically this option
     * selected. Called before the option row is deleted.
     *
     * @return int number of properties affected
     */
    public function retireUtilityOption(UtilityOption $option): int
    {
        $properties = Property::query()
            ->whereHas('utilities', fn ($q) => $q->where('property_utilities.utility_option_id', $option->id))
            ->with('landlord')
            ->get();

        return $this->notifyLandlords(
            $properties,
            $option->utilityType?->name ?? 'Utility',
            $option->label,
        );
    }

    private function notifyLandlords($properties, string $utilityName, ?string $optionLabel): int
    {
        $affected = 0;

        foreach ($properties as $property) {
            if (! $property->landlord) {
                continue;
            }

            $property->landlord->notify(
                new PropertyUtilityRetired($property, $utilityName, $optionLabel)
            );
            $affected++;
        }

        return $affected;
    }
}
