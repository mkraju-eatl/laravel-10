@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <div class="row justify-content-end">
                            <div class="col-md-6">Meeting Lists</div>
                            <div class="col-md-6 justify-content-end"><a class="btn btn-sm btn-primary justify-content-end"
                                                     href="{{ route('add-meeting') }}">Add New</a></div>
                        </div>
                    </div>

                    <div class="card-body">
                        <table class="table table-striped table-bordered table-sm">
                            <thead>
                            <tr>
                                <th>SL</th>
                                <th>Meeting ID</th>
                                <th>Meeting Name</th>
                                <th>Join As</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($meetings as $meeting)
                                <tr>
                                    <td>{{ $loop->index + 1 }}</td>
                                    <td>{{ $meeting->meeting_id }}</td>
                                    <td>{{ $meeting->meeting_name }}</td>
                                    <td>
                                        <a target="_blank" class="btn btn-info btn-sm"
                                           href="{{ $meeting->moderator_url }}">Moderator</a>
                                        <a target="_blank" class="btn btn-success btn-sm"
                                           href="{{ $meeting->attendee_url }}">Attendee</a>
                                    </td>
                                    <td>
                                        <a href="" class="btn btn-primary btn-sm">Assign Role</a>
                                        <a href="{{ route('meeting-info',['meeting_id' => $meeting->meeting_id]) }}" class="btn btn-primary btn-sm">Meeting Info</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
