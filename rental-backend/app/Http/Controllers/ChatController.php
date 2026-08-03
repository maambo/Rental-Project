<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChatController extends Controller
{
    public function index()
    {
        return Inertia::render('Chat/Index', [
            'conversations' => $this->conversations(auth()->id()),
        ]);
    }

    public function show($userId)
    {
        $recipient = User::findOrFail($userId);
        $authId    = auth()->id();

        $messages = Message::where(function ($q) use ($authId, $userId) {
            $q->where('sender_id', $authId)->where('receiver_id', $userId);
        })->orWhere(function ($q) use ($authId, $userId) {
            $q->where('sender_id', $userId)->where('receiver_id', $authId);
        })
        ->with('sender:id,name')
        ->oldest()
        ->get()
        ->map(fn ($m) => [
            'id'        => $m->id,
            'content'   => $m->content,
            'mine'      => $m->sender_id === $authId,
            'sender'    => $m->sender?->name,
            'sent_at'   => $m->created_at->diffForHumans(),
        ]);

        // Mark received messages as read
        Message::where('sender_id', $userId)
            ->where('receiver_id', $authId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return Inertia::render('Chat/Index', [
            'recipient'     => ['id' => $recipient->id, 'name' => $recipient->name],
            'messages'      => $messages,
            'conversations' => $this->conversations($authId),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|integer|exists:users,id',
            'content'     => 'required|string|max:2000',
        ]);

        Message::create([
            'sender_id'   => auth()->id(),
            'receiver_id' => $validated['receiver_id'],
            'content'     => $validated['content'],
            'is_read'     => false,
        ]);

        return redirect()->route('chat.show', $validated['receiver_id']);
    }

    private function conversations(int $userId): array
    {
        $messages = Message::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->with(['sender:id,name', 'receiver:id,name'])
            ->latest()
            ->get();

        $seen = [];
        $result = [];

        foreach ($messages as $msg) {
            $otherId = $msg->sender_id === $userId ? $msg->receiver_id : $msg->sender_id;
            if (isset($seen[$otherId])) {
                continue;
            }
            $seen[$otherId] = true;

            $other = $msg->sender_id === $userId ? $msg->receiver : $msg->sender;

            $unread = Message::where('sender_id', $otherId)
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->count();

            $result[] = [
                'user'         => ['id' => $other?->id, 'name' => $other?->name ?? 'Unknown'],
                'last_message' => $msg->content,
                'last_at'      => $msg->created_at->diffForHumans(),
                'unread_count' => $unread,
            ];
        }

        return $result;
    }
}
