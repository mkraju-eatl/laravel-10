@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Add New Meeting</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('save-meeting') }}">
                            @csrf

                            <div class="row">
                                <div class="col-md-4 pb-3">
                                    <div class="form-group">
                                        <label for="meeting_name" class="col-form-label">Meeting ID</label>
                                        <input type="text" value="{{ $new_meeting_id }}" class="form-control" readonly>
                                    </div>
                                </div>

                                <div class="col-md-4 pb-3">
                                    <div class="form-group">
                                        <label for="meeting_name" class="col-form-label">Meeting Name</label>
                                        <input name="meeting_name" value="{{ old('meeting_name') }}" id="meeting_name"
                                               type="text" placeholder="Meeting Name"
                                               class="form-control @error('meeting_name') is-invalid @enderror">
                                        @error('meeting_name')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4 pb-3">
                                    <div class="form-group">
                                        <label for="record_permission" class="col-form-label">Record Permission</label>
                                        <select name="record_permission" id="record_permission" class="form-select">
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12 pb-3">
                                    <div class="form-group">
                                        <label for="moderators" class="col-form-label">Moderators: </label>
                                        <select name="moderators[]" class="form-select" id="moderators" multiple>
                                            <option value="">Select Moderator</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4 pb-3">
                                    <div class="form-group">
                                        <label for="meeting_type" class="col-form-label">Meeting Type</label>
                                        <select name="meeting_type" id="meeting_type" class="form-select">
                                            <option value="public">Public</option>
                                            <option value="restricted">Restricted</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4 pb-3">
                                    <div class="form-group">
                                        <label for="moderator_password" class="col-form-label">Moderator
                                            Password</label>
                                        <input name="moderator_password" value="{{ old('moderator_password') }}"
                                               id="moderator_password"
                                               type="text" placeholder="Moderator Password"
                                               class="form-control @error('moderator_password') is-invalid @enderror">
                                        @error('moderator_password')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4 pb-3">
                                    <div class="form-group">
                                        <label for="attendee_password" class="col-form-label">Attendee Password</label>
                                        <input name="attendee_password" value="{{ old('attendee_password') }}"
                                               id="attendee_password"
                                               type="text" placeholder="Attendee Password"
                                               class="form-control @error('attendee_password') is-invalid @enderror">
                                        @error('attendee_password')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-12 pb-3">
                                    <div class="form-group">
                                        <label for="attendees" class="col-form-label">Attendee: </label>
                                        <select name="attendees[]" class="form-select" id="attendees" multiple>
                                            <option value="">Select Attendee</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')

@endpush
