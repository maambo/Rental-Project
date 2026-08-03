<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20)
            ->through(fn ($n) => [
                'id'         => $n->id,
                'data'       => $n->data,
                'read_at'    => $n->read_at,
                'created_at' => $n->created_at->diffForHumans(),
            ]);

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
        ]);
    }

    public function recent(Request $request)
    {
        return response()->json(
            $request->user()
                ->notifications()
                ->latest()
                ->take(10)
                ->get()
                ->map(fn ($n) => [
                    'id'         => $n->id,
                    'data'       => $n->data,
                    'read_at'    => $n->read_at,
                    'created_at' => $n->created_at->diffForHumans(),
                ])
        );
    }

    public function markAsRead(Request $request, string $id)
    {
        $request->user()
            ->notifications()
            ->findOrFail($id)
            ->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }
}
