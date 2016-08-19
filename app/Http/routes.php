<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group(array('prefix' => 'v1'), function() {
    
    //Calendar
    Route::get('calendars', 'CalendarController@index', ['only' => ['index']]);
    Route::post('calendars', 'CalendarController@store', ['only' => ['store']]);
});
