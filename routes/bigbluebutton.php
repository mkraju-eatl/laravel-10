<?php

use App\Http\Controllers\BigBlueButtonController;
use Illuminate\Support\Facades\Route;

Route::get('meeting-lists', [BigBlueButtonController::class, 'meetingLists'])->name('meeting-lists');
Route::get('create-meeting', [BigBlueButtonController::class, 'createMeeting'])->name('create-meeting');
Route::get('join-meeting', [BigBlueButtonController::class, 'joinMeeting'])->name('join-meeting');
Route::get('end-meeting', [BigBlueButtonController::class, 'endMeeting'])->name('end-meeting');
Route::get('meeting-info', [BigBlueButtonController::class, 'getMeetingInfo'])->name('meeting-info');

// routes/api.php

// Group routes that require authentication
Route::middleware('auth:sanctum')->controller(\App\Http\Controllers\BigBlueButtonController2::class)->group(function () {
    Route::get('/add-meeting','addMeeting')->name('add-meeting');
    Route::post('/meetings','createMeeting')->name('save-meeting');
    Route::post('/meetings/{meetingID}/join','joinMeeting');
    Route::post('/meetings/{meetingID}/end','endMeeting');
    Route::post('/meetings/{meetingID}/assign-moderator','assignModerator');
    Route::get('/meetings/{meetingID}/participants','listParticipants');
    Route::get('/meetings/{meetingID}','getMeetingDetails');
});

