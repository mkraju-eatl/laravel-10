@extends('layouts.app')

@section('content')
    <div class="row pt-5 pb-5">
        <div class="col-md-12">
            <form action="{{ route('store-meeting') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="form-group col-4 pb-3">
                        <label for="">Meeting ID</label>
                        <input type="text" name="meetingID" class="form-control" value="{{ "raju_".time() }}" placeholder="Meeting ID">
                        @error('meetingID')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group col-4 pb-3">
                        <label for="">Meeting Name</label>
                        <input type="text" name="meetingName" class="form-control" placeholder="Meeting Name" value="{{ "raju_".time() }}">
                        @error('meetingName')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group col-4 pb-3">
                        <label for="">Attendee PW</label>
                        <input type="text" name="attendee_pw" class="form-control" placeholder="Attendee PW">
                        @error('attendee_pw')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group col-4 pb-3">
                        <label for="">moderator PW</label>
                        <input type="text" name="moderator_pw" class="form-control" placeholder="moderator PW">
                        @error('moderator_pw')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group col-4 pb-3">
                        <label for=""></label>
                        <input type="submit"  class="form-control btn btn-info" placeholder="Submit">
                        @error('meeting_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
