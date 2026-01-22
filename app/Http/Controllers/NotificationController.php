<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index()
    {
        // Don't pass data - we fetch it directly in the view
        return view('notifications.index');
    }
    
    public function markAsRead(DatabaseNotification $notification)
    {
        // Simple authorization
        if ($notification->notifiable_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
        
        $notification->markAsRead();
        
        return back()->with('success', 'Notification marked as read');
    }
    
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        
        return back()->with('success', 'All notifications marked as read');
    }
    
    public function destroy(DatabaseNotification $notification)
    {
        // Simple authorization
        if ($notification->notifiable_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
        
        $notification->delete();
        
        return back()->with('success', 'Notification deleted');
    }
    
    public function unreadCount()
    {
        return response()->json([
            'count' => auth()->user()->unreadNotifications->count()
        ]);
    }
}