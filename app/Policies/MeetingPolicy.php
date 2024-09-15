<?php

// app/Policies/MeetingPolicy.php

namespace App\Policies;

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MeetingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can end the meeting.
     */
    public function end(User $user, Meeting $meeting)
    {
        return $meeting->participants()
            ->where('user_id', $user->id)
            ->where('role', 'moderator')
            ->exists();
    }

    /**
     * Determine if the user can assign a moderator.
     */
    public function assignModerator(User $user, Meeting $meeting)
    {
        return $meeting->participants()
            ->where('user_id', $user->id)
            ->where('role', 'moderator')
            ->exists();
    }

    /**
     * Determine if the user can view the meeting details.
     */
    public function view(User $user, Meeting $meeting)
    {
        return $meeting->participants()
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Determine if the user can list participants.
     */
    public function listParticipants(User $user, Meeting $meeting)
    {
        return $meeting->participants()
            ->where('user_id', $user->id)
            ->exists();
    }
}
