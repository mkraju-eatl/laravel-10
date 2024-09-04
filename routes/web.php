<?php

use App\Http\Controllers\MeetingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BigBlueButtonController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::controller(BigBlueButtonController::class)->group(function () {
    Route::get('check-bigblue-conneciton','checkConnection')->name('check-bigblue-conneciton');
    Route::get('create-meeting','createMeeting')->name('create-meeting');
    Route::post('store-meeting','storeMeeting')->name('store-meeting');
});

//Route::post('/create-meeting', [MeetingController::class, 'createMeeting']);
Route::post('store-meeting',[MeetingController::class,'storeMeeting'])->name('store-meeting');
Route::get('/meeting-info/{meetingID}', [MeetingController::class, 'getMeetingInfo']);

Route::controller(\App\Http\Controllers\RedisCheckController::class)->group(function () {
   Route::get('store-on-redis','storeOnRedis')->name('store-on-redis');
});



