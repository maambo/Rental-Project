<?php

namespace App\Http\Controllers;

use App\Models\RentalHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RentalHistoryController extends Controller
{
    /**
     * Display rental history for the authenticated user.
     */
    public function index()
    {
        $user = auth()->user();
        $query = RentalHistory::with(['property', 'tenant', 'landlord']);

        if ($user->hasRole('landlord')) {
            $query->where('landlord_id', $user->id);
        } elseif ($user->hasRole('tenant')) {
            $query->where('tenant_id', $user->id);
        } else {
            // Admins can see everything
        }

        $history = $query->latest('start_date')->paginate(10);

        return Inertia::render('RentalHistory/Index', [
            'history' => $history,
        ]);
    }

    /**
     * Display the specified rental history record.
     */
    public function show(RentalHistory $rentalHistory)
    {
        $user = auth()->user();
        
        // Basic authorization
        if (!$user->roleModel || ($user->roleModel->name !== 'admin' && 
            $rentalHistory->tenant_id !== $user->id && 
            $rentalHistory->landlord_id !== $user->id)) {
            abort(403);
        }

        return Inertia::render('RentalHistory/Show', [
            'record' => $rentalHistory->load(['property', 'tenant', 'landlord']),
        ]);
    }
}
