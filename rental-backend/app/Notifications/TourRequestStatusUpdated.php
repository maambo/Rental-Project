<?php

namespace App\Notifications;

use App\Models\TourRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TourRequestStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(public readonly TourRequest $tourRequest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $status   = $this->tourRequest->status;
        $property = $this->tourRequest->property?->title ?? 'a property';

        $title = match ($status) {
            'approved'     => 'Tour request approved',
            'rejected'     => 'Tour request declined',
            'cancelled'    => 'Tour request cancelled',
            'rescheduled'  => 'Tour rescheduled — action required',
            default        => 'Tour request updated',
        };

        $message = match ($status) {
            'approved'     => "Your tour request for \"{$property}\" has been approved.",
            'rejected'     => "Your tour request for \"{$property}\" was not accepted.",
            'cancelled'    => "Your tour request for \"{$property}\" has been cancelled.",
            'rescheduled'  => "The landlord has proposed a new schedule for your tour at \"{$property}\". Please accept or decline.",
            default        => "Your tour request for \"{$property}\" has been updated.",
        };

        if ($status !== 'rescheduled' && $this->tourRequest->landlord_response) {
            $message .= ' Landlord note: ' . $this->tourRequest->landlord_response;
        }

        return [
            'type'        => 'tour_request_status',
            'title'       => $title,
            'message'     => $message,
            'action_url'  => route('tenant.tour-requests.index'),
            'action_text' => 'View tour requests',
        ];
    }
}
