<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = $user->notifications()->orderByDesc('created_at');

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('read')) {
            $query->where('read', $request->read);
        }

        $notifications = $query->paginate(20);

        return response()->json($notifications);
    }

    public function markRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);

        $notification->update(['read' => true]);

        return response()->json([
            'message' => 'Notification marked as read',
            'notification' => $notification,
        ]);
    }

    public function markAllRead()
    {
        auth()->user()->notifications()
            ->where('read', false)
            ->update(['read' => true]);

        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function unreadCount()
    {
        $count = auth()->user()->notifications()
            ->where('read', false)
            ->count();

        return response()->json(['count' => $count]);
    }
}
