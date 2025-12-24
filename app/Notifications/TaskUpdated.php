<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class TaskUpdated extends Notification
{
    use Queueable;

    protected Task $task;
    protected string $type;

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task, string $type)
    {
        $this->task = $task;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'task_id'    => $this->task->id,
            'project_id' => $this->task->project_id,
            'title'      => $this->task->title,
            'type'       => $this->type, // assigned | completed
        ];
    }
}
