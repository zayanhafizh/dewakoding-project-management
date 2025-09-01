<?php

namespace App\Policies;

use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketCommentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_ticket::comment');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TicketComment $ticketComment): bool
    {
        return $user->can('view_ticket::comment');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_ticket::comment');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TicketComment $ticketComment): bool
    {
        // Super admin can update any comment
        if ($user->hasRole(['super_admin'])) {
            return true;
        }

        // Check if user has general permission to update ticket comments
        if (!$user->can('update_ticket::comment')) {
            return false;
        }

        // User can only update their own comments
        return $ticketComment->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TicketComment $ticketComment): bool
    {
        // Super admin can delete any comment
        if ($user->hasRole(['super_admin'])) {
            return true;
        }

        // Check if user has general permission to delete ticket comments
        if (!$user->can('delete_ticket::comment')) {
            return false;
        }

        // User can only delete their own comments
        return $ticketComment->user_id === $user->id;
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_ticket::comment');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TicketComment $ticketComment): bool
    {
        return $user->can('restore_ticket::comment');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TicketComment $ticketComment): bool
    {
        return $user->can('force_delete_ticket::comment');
    }

    /**
     * Determine whether the user can comment on a specific ticket.
     * This checks if the user is a project member or has appropriate permissions.
     */
    public function createForTicket(User $user, $ticket): bool
    {
        // Super admin can comment on any ticket
        if ($user->hasRole(['super_admin'])) {
            return true;
        }

        // Check if user has general permission to create ticket comments
        if (!$user->can('create_ticket::comment')) {
            return false;
        }

        // Check if user is a member of the project
        $project = $ticket->project;
        return $project->members()->where('users.id', $user->id)->exists();
    }
}