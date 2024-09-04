<?php

namespace App\Http\Controllers;

use App\Services\BigBlueButtonService;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    protected $bbbService;

    public function __construct(BigBlueButtonService $bbbService)
    {
        $this->bbbService = $bbbService;
    }

    public function storeMeeting(Request $request)
    {
        $meetingID = $request->input('meetingID');
        $meetingName = $request->input('meetingName');

        return $response = $this->bbbService->createMeeting($meetingID, $meetingName);

        if ($response->getReturnCode() === 'SUCCESS') {
            return response()->json(['message' => 'Meeting created successfully']);
        }

        return response()->json(['message' => 'Failed to create meeting'], 500);
    }

    public function getMeetingInfo($meetingID)
    {
        $response = $this->bbbService->getMeetingInfo($meetingID);

        if ($response->getReturnCode() === 'SUCCESS') {
            return response()->json($response->getRawResponse());
        }

        return response()->json(['message' => 'Failed to get meeting info'], 500);
    }
}
