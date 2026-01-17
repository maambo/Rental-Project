<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;

class ChatController extends Controller
{
    public function index()
    {
        // Placeholder: Get conversations. 
        // For MVP/Demo, we just pass empty or mock data.
        return Inertia::render('Chat/Index', [
            'conversations' => [], 
        ]);
    }
    
    public function show($userId) 
    {
        $recipient = User::findOrFail($userId);
        
        return Inertia::render('Chat/Index', [
            'recipient' => $recipient,
            'messages' => [], // Fetch messages logic would go here
        ]);
    }
}
