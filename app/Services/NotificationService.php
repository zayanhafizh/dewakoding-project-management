<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    public function notifyCommentAdded(TicketComment $comment): void
    {
        $ticket = $comment->ticket;
        $commenter = $comment->user;
        
        $usersToNotify = $this->getUsersToNotifyForComment($ticket, $commenter);
        
        foreach ($usersToNotify as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'comment_added',
                'title' => 'New Comment on Ticket',
                'message' => "{$commenter->name} added a comment on ticket {$ticket->title}",
                'data' => [
                    'ticket_id' => $ticket->id,
                    'comment_id' => $comment->id,
                    'commenter_id' => $commenter->id,
                    'commenter_name' => $commenter->name,
                ],
            ]);
        }
    }

    public function notifyCommentUpdated(TicketComment $comment): void
    {
        $ticket = $comment->ticket;
        $commenter = $comment->user;
        
        $usersToNotify = $this->getUsersToNotifyForComment($ticket, $commenter);
        
        foreach ($usersToNotify as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'comment_updated',
                'title' => 'Comment Updated',
                'message' => "{$commenter->name} updated a comment on ticket {$ticket->title}",
                'data' => [
                    'ticket_id' => $ticket->id,
                    'comment_id' => $comment->id,
                    'commenter_id' => $commenter->id,
                    'commenter_name' => $commenter->name,
                ],
            ]);
        }
    }

    private function getUsersToNotifyForComment(Ticket $ticket, User $commenter): Collection
    {
        $usersToNotify = collect();
        
        if ($ticket->creator && $ticket->creator->id !== $commenter->id) {
            $usersToNotify->push($ticket->creator);
        }
        
        $assignedUsers = $ticket->assignees()->where('users.id', '!=', $commenter->id)->get();
        $usersToNotify = $usersToNotify->merge($assignedUsers);
        
        $commenters = $ticket->comments()
            ->with('user')
            ->where('user_id', '!=', $commenter->id)
            ->get()
            ->pluck('user')
            ->unique('id');
        $usersToNotify = $usersToNotify->merge($commenters);
        
        return $usersToNotify->unique('id');
    }

    public function markAsRead(int $notificationId, int $userId): bool
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->first();
            
        if ($notification) {
            $notification->markAsRead();
            return true;
        }
        
        return false;
    }

    public function markAllAsRead(int $userId): void
    {
        Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}