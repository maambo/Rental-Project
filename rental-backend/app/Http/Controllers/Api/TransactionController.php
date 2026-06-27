<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PaymentReceivedLandlord;
use App\Models\PropertyApplication;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'phone'  => 'nullable|string',
            'name'   => 'required|string',
            'type'   => 'nullable|string|max:4',
            'nrc'    => 'nullable|string',
            'data'   => 'nullable|array',
        ]);

        $transaction = Transaction::create([
            'UID'             => (string) Str::uuid(),
            'RequestID'       => (string) Str::uuid(),
            'TransactionID'   => 'TXN-' . strtoupper(bin2hex(random_bytes(6))),
            'UserID'          => auth()->id(),
            'NRC'             => $validated['nrc'] ?? null,
            'TransactionDate' => now()->toDateString(),
            'Amount'          => $validated['amount'],
            'Name'            => $validated['name'],
            'Type'            => $validated['type'] ?? 'CARD',
            'Phone'           => $validated['phone'] ?? null,
            'Status'          => 'COMPLETED',
            'Data'            => json_encode($validated['data'] ?? []),
            'Timestamp'       => now(),
        ]);

        if (isset($validated['data']['application_id'])) {
            $application = PropertyApplication::with('property.landlord')
                ->find($validated['data']['application_id']);

            if ($application) {
                Mail::to($application->property->landlord->email)->send(
                    new PaymentReceivedLandlord(
                        $application->property->title,
                        $validated['amount'],
                        auth()->user()->name
                    )
                );
            }
        }

        return response()->json([
            'message'     => 'Payment processed successfully',
            'transaction' => $transaction,
        ], 201);
    }
}
