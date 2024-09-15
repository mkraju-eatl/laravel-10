<?php

// app/Http/Controllers/BigBlueButtonController.php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Participant;
use App\Models\User;
use BigBlueButton\BigBlueButton;
use BigBlueButton\Parameters\CreateMeetingParameters;
use BigBlueButton\Parameters\JoinMeetingParameters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BigBlueButtonController2 extends Controller
{
    protected $bbb;

    public function __construct(BigBlueButton $bbb)
    {
        $this->bbb = $bbb;
    }

    /**
     * Add meeting form
     */
    public function addMeeting() {
        $new_meeting_id = Meeting::getMeetingSerial();
        $users = User::select('id','name')->get();
        return view('meetings.add_meeting',compact('new_meeting_id','users'));
    }

    /**
     * Create a new meeting
     */
    public function createMeeting(Request $request)
    {
        $this->validate($request,[
            'meeting_name' => 'required|string|max:255',
        ]);

        $meetingID = 'meeting-' . time(); // Ensure unique meeting ID
        $meetingName = $request->input('meeting_name');
        $moderatorPassword = 'mp'; // Ideally, generate secure random passwords
        $attendeePassword = 'ap';

        $meetingParams = new CreateMeetingParameters($meetingID, $meetingName);
        $meetingParams->setAttendeePassword($attendeePassword);
        $meetingParams->setModeratorPassword($moderatorPassword);

        $response = $this->bbb->createMeeting($meetingParams);

        if ($response->getReturnCode() === 'SUCCESS') {
            $bbbServerUrl = rtrim(env('BBB_SERVER_URL'), '/');

            // Construct the URLs for moderator and attendee
            $moderatorUrl = $bbbServerUrl . '/bigbluebutton/api/join?meetingID=' . urlencode($meetingID) .
                '&password=' . urlencode($moderatorPassword) .
                '&fullName=' . urlencode(Auth::user()->name);
            $attendeeUrl = $bbbServerUrl . '/bigbluebutton/api/join?meetingID=' . urlencode($meetingID) .
                '&password=' . urlencode($attendeePassword) .
                '&fullName=' . urlencode(Auth::user()->name);

            // Save the meeting details to the database
            $meeting = Meeting::create([
                'meeting_id' => $meetingID,
                'meeting_name' => $meetingName,
                'moderator_password' => $moderatorPassword,
                'attendee_password' => $attendeePassword,
                'moderator_url' => $moderatorUrl,
                'attendee_url' => $attendeeUrl,
            ]);

            // Assign the creator as a moderator
            Participant::create([
                'meeting_id' => $meeting->id,
                'user_id' => Auth::id(),
                'role' => 'moderator',
            ]);

            return response()->json([
                'message' => 'Meeting created successfully!',
                'meeting' => $meeting
            ], 201);
        }

        return response()->json(['message' => 'Failed to create meeting!'], 500);
    }

    /**
     * Join a meeting as moderator or attendee
     */
    public function joinMeeting(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'meeting_id' => 'required|string|exists:meetings,meeting_id',
            'role' => 'required|in:moderator,attendee',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $role = $request->input('role');
        $meetingID = $request->input('meeting_id');

        // Retrieve the meeting
        $meeting = Meeting::where('meeting_id', $meetingID)->first();

        if (!$meeting) {
            return response()->json(['message' => 'Meeting not found!'], 404);
        }

        // Check if the user is assigned to this meeting
        $participant = Participant::where('meeting_id', $meeting->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($role === 'moderator') {
            // Verify if user is a moderator
            if (!$participant || $participant->role !== 'moderator') {
                return response()->json(['message' => 'Unauthorized to join as moderator.'], 403);
            }
            $password = $meeting->moderator_password;
        } else {
            $password = $meeting->attendee_password;
        }

        $bbbServerUrl = rtrim(env('BBB_SERVER_URL'), '/');

        // Generate the join URL dynamically
        $joinUrl = $bbbServerUrl . '/bigbluebutton/api/join?meetingID=' . urlencode($meetingID) .
            '&password=' . urlencode($password) .
            '&fullName=' . urlencode(Auth::user()->name);

        return redirect()->away($joinUrl);
    }

    /**
     * End a meeting
     */
    public function endMeeting(Request $request)
    {
        //$this->authorize('end', $meeting);
        // Validate input
        $validator = Validator::make($request->all(), [
            'meeting_id' => 'required|string|exists:meetings,meeting_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $meetingID = $request->input('meeting_id');

        // Retrieve the meeting
        $meeting = Meeting::where('meeting_id', $meetingID)->first();

        if (!$meeting) {
            return response()->json(['message' => 'Meeting not found!'], 404);
        }

        // Ensure the user is a moderator
        $participant = Participant::where('meeting_id', $meeting->id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$participant || $participant->role !== 'moderator') {
            return response()->json(['message' => 'Only moderators can end the meeting.'], 403);
        }

        // Use BBB API to end the meeting
        $endMeetingParams = new \BigBlueButton\Parameters\EndMeetingParameters($meetingID, $meeting->moderator_password);
        $response = $this->bbb->endMeeting($endMeetingParams);

        if ($response->getReturnCode() === 'SUCCESS') {
            return response()->json(['message' => 'Meeting ended successfully!']);
        }

        return response()->json(['message' => 'Failed to end meeting!'], 500);
    }

    /**
     * Assign a user as a moderator for a meeting
     */
    public function assignModerator(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'meeting_id' => 'required|string|exists:meetings,meeting_id',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $meetingID = $request->input('meeting_id');
        $userID = $request->input('user_id');

        // Retrieve the meeting
        $meeting = Meeting::where('meeting_id', $meetingID)->first();

        if (!$meeting) {
            return response()->json(['message' => 'Meeting not found!'], 404);
        }

        // Ensure the requester is a moderator
        $currentParticipant = Participant::where('meeting_id', $meeting->id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$currentParticipant || $currentParticipant->role !== 'moderator') {
            return response()->json(['message' => 'Only moderators can assign roles.'], 403);
        }

        // Assign the user as a moderator
        $participant = Participant::where('meeting_id', $meeting->id)
            ->where('user_id', $userID)
            ->first();

        if ($participant) {
            $participant->role = 'moderator';
            $participant->save();
        } else {
            // If the user is not yet a participant, add them
            Participant::create([
                'meeting_id' => $meeting->id,
                'user_id' => $userID,
                'role' => 'moderator',
            ]);
        }

        return response()->json(['message' => 'User assigned as moderator successfully!']);
    }

    /**
     * List participants of a meeting
     */
    public function listParticipants(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'meeting_id' => 'required|string|exists:meetings,meeting_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $meetingID = $request->input('meeting_id');

        // Retrieve the meeting
        $meeting = Meeting::where('meeting_id', $meetingID)->first();

        if (!$meeting) {
            return response()->json(['message' => 'Meeting not found!'], 404);
        }

        // Ensure the requester is a participant (optional)
        $participant = Participant::where('meeting_id', $meeting->id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$participant) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        // Get participants
        $participants = $meeting->participants()->with('user')->get()->map(function ($participant) {
            return [
                'user_id' => $participant->user->id,
                'name' => $participant->user->name,
                'role' => $participant->role,
                'joined_at' => $participant->created_at->toDateTimeString(),
            ];
        });

        return response()->json(['participants' => $participants]);
    }

    /**
     * Retrieve meeting details including URLs
     */
    public function getMeetingDetails(Request $request, $meetingID)
    {
        // Retrieve the meeting
        $meeting = Meeting::where('meeting_id', $meetingID)->first();

        if (!$meeting) {
            return response()->json(['message' => 'Meeting not found!'], 404);
        }

        // Optionally, restrict access to assigned users
        $participant = Participant::where('meeting_id', $meeting->id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$participant) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        return response()->json([
            'meeting_id' => $meeting->meeting_id,
            'meeting_name' => $meeting->meeting_name,
            'moderator_url' => $meeting->moderator_url,
            'attendee_url' => $meeting->attendee_url,
            'created_at' => $meeting->created_at->toDateTimeString(),
        ]);
    }
}

