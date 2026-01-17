<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blacklist;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BlacklistController extends Controller
{
    public function index()
    {
        $blacklist = Blacklist::orderBy('created_at', 'desc')->paginate(20);
        return Inertia::render('Admin/Blacklist/Index', [
            'blacklist' => $blacklist
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nrc_passport' => 'required|string|unique:blacklists',
            'phone_number' => 'nullable|string',
            'email' => 'nullable|email',
            'reason' => 'required|string',
            'type' => 'required|in:fraud,harassment,fake_listings,other',
        ]);

        Blacklist::create([
            ...$validated,
            'blacklisted_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'User added to blacklist.');
    }

    public function destroy(Blacklist $blacklist)
    {
        $blacklist->delete();
        return redirect()->back()->with('success', 'User removed from blacklist.');
    }
}
