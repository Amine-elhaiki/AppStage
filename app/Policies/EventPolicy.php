<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EventPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Event $event): bool
    {
        return $event->canBeViewedBy($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isActive();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Event $event): bool
    {
        return $event->canBeEditedBy($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Event $event): bool
    {
        return $user->isAdmin() || $event->id_organisateur === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Event $event): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Event $event): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can manage participants.
     */
    public function manageParticipants(User $user, Event $event): bool
    {
        return $user->isAdmin() || $event->id_organisateur === $user->id;
    }

    /**
     * Determine whether the user can join the event.
     */
    public function join(User $user, Event $event): bool
    {
        return $user->isActive() && !$event->isPast();
    }

    /**
     * Determine whether the user can leave the event.
     */
    public function leave(User $user, Event $event): bool
    {
        return $user->isActive() &&
               $event->hasParticipant($user->id) &&
               !$event->isPast() &&
               $event->id_organisateur !== $user->id;
    }

    /**
     * Determine whether the user can change participation status.
     */
    public function changeParticipationStatus(User $user, Event $event): bool
    {
        return $user->isActive() &&
               $event->hasParticipant($user->id) &&
               !$event->isPast();
    }

    /**
     * Determine whether the user can mark attendance.
     */
    public function markAttendance(User $user, Event $event): bool
    {
        return $user->isAdmin() ||
               $event->id_organisateur === $user->id ||
               ($event->isHappeningNow() && $event->hasParticipant($user->id));
    }

    /**
     * Determine whether the user can cancel the event.
     */
    public function cancel(User $user, Event $event): bool
    {
        return ($user->isAdmin() || $event->id_organisateur === $user->id) &&
               !$event->isPast() &&
               $event->statut !== 'annule';
    }

    /**
     * Determine whether the user can postpone the event.
     */
    public function postpone(User $user, Event $event): bool
    {
        return ($user->isAdmin() || $event->id_organisateur === $user->id) &&
               !$event->isPast() &&
               $event->statut !== 'termine';
    }

    /**
     * Determine whether the user can view event statistics.
     */
    public function viewStats(User $user, Event $event): bool
    {
        return $event->canBeViewedBy($user);
    }
}
