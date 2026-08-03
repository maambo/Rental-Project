<?php

namespace App\Notifications;

use App\Models\TradeCategory;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to a worker when the trade category their profile is built around is
 * retired by an admin. Until they pick a new category their profile is hidden
 * from the marketplace, so the message needs to say that plainly.
 */
class TradeCategoryRetired extends Notification
{
    use Queueable;

    public function __construct(public readonly TradeCategory $category) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'        => 'trade_category_retired',
            'title'       => 'Your skill is no longer available',
            'message'     => "\"{$this->category->name}\" has been retired. Your profile is hidden from the marketplace until you choose a new skill.",
            'action_url'  => route('worker.profile.edit'),
            'action_text' => 'Update profile',
            'category_id' => $this->category->id,
        ];
    }
}
