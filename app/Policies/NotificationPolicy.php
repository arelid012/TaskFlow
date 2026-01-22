<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

class NotificationPolicy
{
    /**
     * Determine if the user can update the notification.
     */
    public function update(User $user, DatabaseNotification $notification): bool
    {
        return $notification->notifiable_type === User::class 
            && $notification->notifiable_id === $user->id;
    }
    
    /**
     * Determine if the user can delete the notification.
     */
    public function delete(User $user, DatabaseNotification $notification): bool
    {
        return $notification->notifiable_type === User::class 
            && $notification->notifiable_id === $user->id;
    }
}