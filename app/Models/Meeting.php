<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasFactory;

    protected $appends = ['moderator_join', 'attendee_join'];
    protected $fillable = [
        'meeting_id',
        'meeting_name',
        'moderator_password',
        'attendee_password',
        'moderator_url',
        'attendee_url',
    ];

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    public function getModeratorJoinAttribute()
    {
        return env('BACKEND_BASE') . '/join-meeting?role=moderator&meeting_id=' . $this->meegint_id . '&full_name=' . auth()->user()->name;
    }

    public function getAttendeeJoinAttribute()
    {
        return env('BACKEND_BASE') . '/join-meeting?role=moderator&meeting_id=' . $this->meegint_id . '&full_name=' . auth()->user()->name;
    }

    public static function getMeetingSerial()
    {
        $latestSerial = Meeting::latest('id')->value('meeting_serial');
        if (!$latestSerial) {
            return '00001';
        }
        $newSerial = $latestSerial + 1;
        $paddedSerial = str_pad($newSerial, 5, '0', STR_PAD_LEFT);
        if (Meeting::where('meeting_serial', $paddedSerial)->exists()) {
            return self::getMeetingSerial();
        }
        return $paddedSerial;
    }
}
