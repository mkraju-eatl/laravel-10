<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\User;
use BigBlueButton\BigBlueButton;
use BigBlueButton\Parameters\CreateMeetingParameters;
use BigBlueButton\Parameters\GetMeetingInfoParameters;
use BigBlueButton\Parameters\JoinMeetingParameters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BigBlueButtonController extends Controller
{
    protected $bbb;

    public function __construct(BigBlueButton $bbb)
    {
        $this->bbb = $bbb;
    }

    public function meetingLists()
    {
        $meetings = Meeting::latest()->get();
        return view('meetings.meeting_lists', compact('meetings'));
    }

    public function createMeeting(Request $request)
    {
        $meetingID = 'edutube_' . time(); // Generate a unique meeting ID
        $meetingParams = new CreateMeetingParameters($meetingID, 'Edutube First Meeting');
        $meetingParams->setAttendeePassword('mp_123456');
        $meetingParams->setModeratorPassword('ap_123456');
        $meetingParams->setRecord(true); // Allow recording

        $response = $this->bbb->createMeeting($meetingParams);

        if ($response->getReturnCode() === 'SUCCESS') {
            //$bbbServerUrl = env('BBB_SERVER_BASE_URL');

            $moderatorUrl = url('/') . '/join-meeting?role=moderator&meeting_id=' . $meetingID;
            $attendeeUrl = url('/') . '/join-meeting?role=attendee&meeting_id=' . $meetingID;

            // Save the meeting details and URLs to the database
            $meeting = new Meeting();
            $meeting->created_by = Auth::id();
            $meeting->meeting_id = $meetingID;
            $meeting->meeting_name = 'Edutube First Meeting';
            $meeting->moderator_password = 'mp_123456';
            $meeting->attendee_password = 'ap_123456';
            $meeting->moderator_url = $moderatorUrl; // Save the moderator URL
            $meeting->attendee_url = $attendeeUrl; // Save the attendee URL
            $meeting->save();
            return redirect()->route('meeting-lists');
            // return response()->json(['message' => 'Meeting created successfully!', 'meeting' => $meeting]);
        }

        return response()->json(['message' => 'Failed to create meeting!'], 500);
    }


    public function joinMeeting(Request $request)
    {
        $role = $request->get('role'); // 'moderator' or 'attendee'
        $meetingID = $request->get('meeting_id');

        // Retrieve the meeting from the database
        $meeting = Meeting::where('meeting_id', $meetingID)->firstOrFail();

        $password = $role === 'moderator' ? $meeting->moderator_password : $meeting->attendee_password;
        $joinParams = new JoinMeetingParameters($meeting->meeting_id, 'User Name', $password);
        $joinParams->setRedirect(true);

        return redirect()->away($this->bbb->getJoinMeetingURL($joinParams));
    }

    public function getMeetingLinks($meetingID)
    {
        $meeting = Meeting::where('meeting_id', $meetingID)->firstOrFail();

        return response()->json([
            'moderator_url' => $meeting->moderator_url,
            'attendee_url' => $meeting->attendee_url,
        ]);
    }

    public function assignModerator(Request $request, $meetingId)
    {
        $user = User::find($request->user_id); // Find the user you want to assign
        $user->role = 'moderator';
        $user->save();

        return response()->json(['message' => 'User assigned as moderator']);
    }

    public function endMeeting(Request $request)
    {
        $endMeetingParams = new \BigBlueButton\Parameters\EndMeetingParameters('meeting-id', 'mp');

        $response = $this->bbb->endMeeting($endMeetingParams);

        if ($response->getReturnCode() === 'SUCCESS') {
            return response()->json(['message' => 'Meeting ended successfully!']);
        }

        return response()->json(['message' => 'Failed to end meeting!'], 500);
    }

    public function getMeetingInfo()
    {
        $meetingID = request()->get('meeting_id');
        $moderatorPassword = 'mp_123456';

        // Create the GetMeetingInfoParameters object
        $meetingInfoParams = new GetMeetingInfoParameters($meetingID, $moderatorPassword);

        // Call getMeetingInfo with the meetingInfoParams object
        $response = $this->bbb->getMeetingInfo($meetingInfoParams);

        // Check if the response was successful
        if ($response->getReturnCode() === 'SUCCESS') {
            // Process the meeting info and return it as a JSON response
            $meetingInfo = $response->getMeetingInfo();
            return response()->json($meetingInfo);
        }

        // If the return code is not 'SUCCESS', return a failure message
        return response()->json(['message' => 'Failed to get meeting info'], 500);
    }


    public function getRecordings()
    {
        $meetingID = request()->get('meeting_id');
        $response = $this->bbb->getRecordings($meetingID);

        if ($response->getReturnCode() === 'SUCCESS') {
            $recordings = $response->getRecordings($meetingID); // Returns an array of recording objects
        }

    }

}


