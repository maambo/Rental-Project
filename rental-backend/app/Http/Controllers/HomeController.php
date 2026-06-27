<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $properties = Property::with(['images', 'landlord'])
            ->visibleInSearch()
            ->latest()
            ->take(6)
            ->get();

        return Inertia::render('Welcome', [
            'canLogin'    => route('login') !== null,
            'canRegister' => route('register') !== null,
            'properties'  => $properties,
        ]);
    }
}
