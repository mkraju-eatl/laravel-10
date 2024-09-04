<?php
namespace App\Services;

use BigBlueButton\BigBlueButton;
use BigBlueButton\Parameters\CreateMeetingParameters;
use BigBlueButton\Parameters\GetMeetingInfoParameters;

class BigBlueButtonService
{
    protected $bbb;

    public function __construct()
    {
        $baseUrl = env('BBB_SERVER_BASE_URL');
        $secret = env('BBB_SECRET');

        $this->bbb = new BigBlueButton($baseUrl, $secret);
    }

    public function createMeeting($meetingID, $meetingName)
    {
        $params = new CreateMeetingParameters($meetingID, $meetingName);
        return $this->bbb->createMeeting($params);
    }

    public function getMeetingInfo($meetingID)
    {
        $params = new GetMeetingInfoParameters($meetingID);
        return $this->bbb->getMeetingInfo($params);
    }
}

