<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;

class LandlordProfileController extends Controller
{
    public function index()
    {
        $landlords = User::with(['roleModel', 'landlordApplication', 'properties'])
            ->whereHas('roleModel', fn($q) => $q->where('name', 'landlord'))
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Admin/Landlords/Index', [
            'landlords' => $landlords,
        ]);
    }

    public function show($id)
    {
        $landlord = User::with([
            'landlordApplication',
            'properties' => fn($q) => $q->orderByDesc('created_at'),
        ])->findOrFail($id);

        return Inertia::render('Admin/Landlords/Show', [
            'landlord'   => $landlord,
            'properties' => $landlord->properties,
        ]);
    }
}
