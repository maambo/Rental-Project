<?php

namespace App\Console\Commands;

use App\Mail\ApplicationStatusTenant;
use App\Models\PropertyApplication;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ExpirePaymentDeadlines extends Command
{
    protected $signature   = 'applications:expire-deadlines';
    protected $description = 'Auto-reject payment_requested applications whose deadline has passed';

    public function handle(): void
    {
        $expired = PropertyApplication::paymentExpired()
            ->with(['property', 'user'])
            ->get();

        foreach ($expired as $application) {
            $application->update([
                'status'           => 'rejected',
                'rejection_reason' => 'Payment deadline passed. The application was automatically rejected.',
            ]);

            if ($application->user?->email) {
                Mail::to($application->user->email)->send(
                    new ApplicationStatusTenant(
                        $application->user,
                        $application->property->title,
                        'rejected',
                        'Your payment deadline passed and the application was automatically rejected.'
                    )
                );
            }
        }

        $this->info("Expired {$expired->count()} application(s).");
    }
}
