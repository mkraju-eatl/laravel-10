@extends('layouts.app')

@section('content')
    <div class="row pt-10">
        <div class="col-md-12">
            @isset($connection_status)
                <p class="alert alert-{{ $connection_status == true ? "success" : "danger" }}">{{ $connection_status == true ? "Connected" : "Not connected" }}</p>
            @endisset
        </div>
    </div>
@endsection
