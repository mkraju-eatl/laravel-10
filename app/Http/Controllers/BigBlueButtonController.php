<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JoisarJignesh\Bigbluebutton\Facades\Bigbluebutton;

class BigBlueButtonController extends Controller
{
    public function checkConnection()
    {
        $connection_status = Bigbluebutton::isConnect(); //by default
        //return view('welcome',compact('connection_status'));
        dd(Bigbluebutton::server('server1')->isConnect()); //for specific server
    }

    public function createMeeting() {
        return view('create_meeting');
    }

    public function storeMeeting(Request $request)
    {
        Bigbluebutton::create([
    'meetingID' => 'tamku',
    'meetingName' => 'test meeting',
    'attendeePW' => 'attendee',
    'moderatorPW' => 'moderator'
]);
        $this->validate($request,[
            'meeting_id' => 'required',
            'meeting_name' => 'required',
            'attendee_pw' => 'required',
            'moderator_pw' => 'required',
        ]);
        $meeting_data = [
            'meetingID' => $request->meeting_id,
            'meetingName' => $request->meeting_name,
            'attendeePW' => $request->attendee_pw,
            'moderatorPW' => $request->moderator_pw,
        ];
        \Bigbluebutton::create($meeting_data);
        return "Done";
    }
}
