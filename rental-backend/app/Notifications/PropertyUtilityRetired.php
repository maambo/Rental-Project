<?php

namespace App\Notifications;

use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to a landlord when a utility type — or a specific option they had
 * selected — is retired by an admin. Unlike a worker's trade category, a
 * utility is optional extra detail, so the listing stays live and searchable;
 * the landlord is just asked to refresh the stale field.
 */
class PropertyUtilityRetired extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Property $property,
        public readonly string $utilityName,
        public readonly ?string $optionLabel = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $what = $this->optionLabel
            ? "The \"{$this->optionLabel}\" option for \"{$this->utilityName}\""
            : "The utility \"{$this->utilityName}\"";

        return [
            'type'         => 'property_utility_retired',
            'title'        => 'A utility on your property was retired',
            'message'      => "{$what} is no longer available. Please update \"{$this->property->title}\" — the listing stays visible in the meantime.",
            'action_url'   => route('landlord.properties.edit', $this->property->id),
            'action_text'  => 'Update property',
            'property_id'  => $this->property->id,
            'utility_name' => $this->utilityName,
        ];
    }
}
