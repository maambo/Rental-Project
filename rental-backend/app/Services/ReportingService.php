<?php

namespace App\Services;

use App\Models\Billing;
use App\Models\JobBooking;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Single source of truth for every financial number shown anywhere in the app —
 * Admin\StatisticsController and the Reports module both read through this class
 * so "total revenue" can never disagree between the two pages.
 */
class ReportingService
{
    /**
     * Completed transaction revenue grouped by day or month.
     */
    public function revenueByPeriod(CarbonInterface $from, CarbonInterface $to, string $groupBy = 'month'): Collection
    {
        $format = $groupBy === 'day' ? 'Y-m-d' : 'Y-m';

        return Transaction::where('Status', 'COMPLETED')
            ->whereBetween('created_at', [$from, $to])
            ->get()
            ->groupBy(fn ($t) => $t->created_at->format($format))
            ->map(fn ($group, $period) => [
                'period' => $period,
                'total' => (float) $group->sum('Amount'),
                'count' => $group->count(),
            ])
            ->sortBy('period')
            ->values();
    }

    public function totalRevenue(): float
    {
        return (float) Transaction::where('Status', 'COMPLETED')->sum('Amount');
    }

    public function averageTransaction(): float
    {
        return (float) (Transaction::where('Status', 'COMPLETED')->avg('Amount') ?? 0);
    }

    /**
     * Per-property rent income for a single landlord over a date range.
     */
    public function landlordIncomeBreakdown(User $landlord, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return Billing::whereHas('leaseAgreement', fn ($q) => $q->where('landlord_id', $landlord->id))
            ->whereBetween('Date', [$from, $to])
            ->with('leaseAgreement.property')
            ->get()
            ->groupBy(fn ($b) => $b->leaseAgreement?->property_id ?? 0)
            ->map(fn ($group) => [
                'property_id' => $group->first()->leaseAgreement?->property_id,
                'property' => $group->first()->leaseAgreement?->property?->title ?? 'Unknown property',
                'total_billed' => (float) $group->sum('Amount'),
                'total_paid' => (float) $group->where('status', 'paid')->sum('Amount'),
                'outstanding' => (float) $group->where('status', '!=', 'paid')->sum('Amount'),
                'bill_count' => $group->count(),
            ])
            ->values();
    }

    /**
     * Drill-through: every bill for one of a landlord's properties over a date range —
     * the detail rows behind a single line of landlordIncomeBreakdown().
     */
    public function landlordPropertyBillingDetail(User $landlord, int $propertyId, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return Billing::whereHas(
            'leaseAgreement',
            fn ($q) => $q->where('landlord_id', $landlord->id)->where('property_id', $propertyId),
        )
            ->whereBetween('Date', [$from, $to])
            ->with('leaseAgreement.property')
            ->orderByDesc('Date')
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'description' => $b->Description,
                'amount' => (float) $b->Amount,
                'status' => $b->status,
                'date' => $b->Date?->toDateString(),
                'paid_at' => $b->paid_at?->toDateString(),
            ]);
    }

    /**
     * Drill-through: every completed transaction in a single revenue-by-period bucket.
     */
    public function transactionsInPeriod(string $period, string $groupBy = 'month'): Collection
    {
        $format = $groupBy === 'day' ? 'Y-m-d' : 'Y-m';

        return Transaction::where('Status', 'COMPLETED')
            ->with('owner')
            ->get()
            ->filter(fn ($t) => $t->created_at->format($format) === $period)
            ->map(fn ($t) => [
                'id' => $t->id,
                'transaction_id' => $t->TransactionID,
                'user' => $t->owner?->name ?? $t->Name,
                'amount' => (float) $t->Amount,
                'type' => $t->Type,
                'date' => $t->created_at->toDateTimeString(),
            ])
            ->values();
    }

    /**
     * A worker's net earnings grouped by service, over a date range — only counting
     * completed job bookings (the only bookings that generate real earnings).
     */
    public function workerIncomeBreakdown(User $worker, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return JobBooking::whereHas('workerProfile', fn ($q) => $q->where('user_id', $worker->id))
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$from, $to])
            ->with('workerService')
            ->get()
            ->groupBy(fn ($b) => $b->workerService?->service_name ?? 'General')
            ->map(fn ($group, $service) => [
                'service' => $service,
                'jobs_completed' => $group->count(),
                'gross_earnings' => (float) $group->sum('agreed_price'),
                'platform_fees' => (float) $group->sum('platform_fee'),
                'net_earnings' => (float) $group->sum('worker_net'),
            ])
            ->values();
    }

    /**
     * Drill-through: every completed booking behind a single service line of
     * workerIncomeBreakdown().
     */
    public function workerServiceBookingDetail(User $worker, string $serviceName, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return JobBooking::whereHas('workerProfile', fn ($q) => $q->where('user_id', $worker->id))
            ->whereHas('workerService', fn ($q) => $q->where('service_name', $serviceName))
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$from, $to])
            ->with('client')
            ->orderByDesc('completed_at')
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'client' => $b->client?->name ?? 'Unknown',
                'agreed_price' => (float) $b->agreed_price,
                'platform_fee' => (float) $b->platform_fee,
                'worker_net' => (float) $b->worker_net,
                'completed_at' => $b->completed_at?->toDateString(),
            ]);
    }

    /**
     * A tenant's own billing/payment history over a date range.
     */
    public function tenantPaymentHistory(User $tenant, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return Billing::where('UserID', (string) $tenant->id)
            ->whereBetween('Date', [$from, $to])
            ->with('leaseAgreement.property')
            ->orderByDesc('Date')
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'property' => $b->leaseAgreement?->property?->title ?? 'Unknown property',
                'description' => $b->Description,
                'amount' => (float) $b->Amount,
                'status' => $b->status,
                'date' => $b->Date?->toDateString(),
                'paid_at' => $b->paid_at?->toDateString(),
            ]);
    }

    /**
     * All unpaid bills platform-wide, for the admin outstanding-balances report.
     */
    public function outstandingBalances(): Collection
    {
        return Billing::where('status', 'pending')
            ->with(['user', 'leaseAgreement.property'])
            ->orderBy('Date')
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'tenant' => $b->user?->name ?? $b->UserID,
                'property' => $b->leaseAgreement?->property?->title ?? 'Unknown property',
                'amount' => (float) $b->Amount,
                'due_date' => $b->Date?->toDateString(),
                'days_overdue' => $b->Date ? now()->diffInDays($b->Date, false) * -1 : 0,
            ]);
    }

    /**
     * New subscription revenue grouped by tier type over a date range.
     */
    public function subscriptionRevenue(CarbonInterface $from, CarbonInterface $to): Collection
    {
        return Subscription::whereBetween('starts_at', [$from, $to])
            ->with('tier')
            ->get()
            ->groupBy(fn ($s) => $s->tier?->tier_type ?? 'unknown')
            ->map(fn ($group, $tierType) => [
                'tier_type' => $tierType,
                'count' => $group->count(),
                'total' => (float) $group->sum(fn ($s) => $s->tier?->price_amount ?? 0),
            ])
            ->values();
    }
}
