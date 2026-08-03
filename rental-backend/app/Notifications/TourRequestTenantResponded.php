<?php

namespace App\Notifications;

use App\Models\TourRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TourRequestTenantResponded extends Notification
{
    use Queueable;

    public function __construct(public readonly TourRequest $tourRequest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $property = $this->tourRequest->property?->title ?? 'a property';
        $tenant   = $this->tourRequest->user?->name ?? 'The tenant';
        $status   = $this->tourRequest->status;

        [$title, $message] = match ($status) {
            'approved' => [
                'Tour accepted by tenant',
                "{$tenant} has accepted your rescheduled tour for \"{$property}\".",
            ],
            default => [
                'Tour declined by tenant',
                "{$tenant} has declined your rescheduled tour for \"{$property}\". The request is back to pending.",
            ],
        };

        return [
            'type'        => 'tour_request_tenant_response',
            'title'       => $title,
            'message'     => $message,
            'action_url'  => route('landlord.tour-requests.index'),
            'action_text' => 'View tour requests',
        ];
    }
}
