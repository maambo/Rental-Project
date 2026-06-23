<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class LedgerController extends Controller
{
    /**
     * Display the tenant's ledger.
     */
    public function index()
    {
        $tenantId = auth()->id();
        
        // Get all billing records for the tenant
        $entries = Billing::where('UserID', $tenantId)
            ->with(['leaseAgreement.property'])
            ->orderBy('Date', 'asc')
            ->get();

        $balance = 0;
        $ledger = $entries->map(function ($entry) use (&$balance) {
            $debit = $entry->Amount; // Debit: Invoice/Amount due
            $credit = $entry->status === 'paid' ? $entry->Amount : 0; // Credit: Payment made
            $balance += ($debit - $credit);

            return [
                'id' => $entry->id,
                'date' => $entry->Date->toDateString(),
                'description' => $entry->Description,
                'reference' => $entry->PackageName ?? 'Invoice',
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $balance,
                'status' => $entry->status,
                'property' => $entry->leaseAgreement?->property?->title ?? 'N/A',
            ];
        });

        return Inertia::render('Tenant/Ledger/Index', [
            'ledger' => $ledger->reverse()->values(),
            'currentBalance' => $balance,
        ]);
    }
}
