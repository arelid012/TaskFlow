<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CustomVerifyEmail extends VerifyEmail implements ShouldQueue
{
    use Queueable;


    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify Your Email Address - TaskFlow')
            ->greeting('Welcome to TaskFlow, ' . $notifiable->name . '!')
            ->line('Thank you for registering with TaskFlow. To get started with managing your projects and tasks, please verify your email address.')
            ->action('Verify Email Address', $verificationUrl)
            ->line('This verification link will expire in 60 minutes.')
            ->line('If you did not create a TaskFlow account, please ignore this email.')
            ->salutation('Best regards,<br>The TaskFlow Team');
    }
}