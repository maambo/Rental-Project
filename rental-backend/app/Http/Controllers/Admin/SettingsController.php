<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        // Will be extended with system_settings (key/value) management
        // in the PostgreSQL migration phase.
        return Inertia::render('Admin/Settings/Index');
    }

    public function update()
    {
        return back()->with('info', 'System settings management coming soon.');
    }
}
