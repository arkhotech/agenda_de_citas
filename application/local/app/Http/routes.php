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
    Route::get('calendars', 'CalendarController@index');    
    Route::get('calendars/searchByName', 'CalendarController@searchByName');
    Route::get('calendars/listByOwner/{owner_id}', 'CalendarController@listByOwner');
    Route::get('calendars/{id}', 'CalendarController@findById');
    Route::post('calendars', 'CalendarController@store');
    Route::put('calendars/{id}', 'CalendarController@update');
    Route::put('calendars/disable/{id}', 'CalendarController@disable');
    
    //DayOff
    Route::get('daysOff', 'DayOffController@index');
    Route::post('daysOff', 'DayOffController@store');
    Route::post('daysOff/bulkLoad', 'DayOffController@bulkLoad');
    Route::delete('daysOff/{id}', 'DayOffController@destroy');
    
    //Appointment    
    Route::get('appointments/{id}', 'AppointmentController@findById');
    Route::post('appointments/reserve', 'AppointmentController@store');
    Route::put('appointments/{id}', 'AppointmentController@update');
    Route::put('appointments/confirm/{id}', 'AppointmentController@confirm');
    Route::post('appointments/bulkConfirm', 'AppointmentController@bulkConfirm');
    Route::put('appointments/cancel/{id}', 'AppointmentController@cancel');
    Route::put('appointments/assists/{id}', 'AppointmentController@assists');
    Route::get('appointments/listByCalendar/{id}', 'AppointmentController@listByCalendar');
    Route::get('appointments/listByApplier/{id}', 'AppointmentController@listByApplier');
    Route::get('appointments/listByOwner/{id}', 'AppointmentController@listByOwner');
    Route::get('appointments/availability/{id}', 'AppointmentController@listAvailability');
    Route::get('appointments/availability/listByOwner/{id}', 'AppointmentController@listAvailabilityByOwner');
    Route::delete('appointments/deleteAppointmentsPendingToConfirm', 'AppointmentController@destroyAppointmentsPendingToConfirm');
    
    //BlockSchedule
    Route::get('blockSchedules/listByCalendarId/{calendar_id}', 'BlockScheduleController@index');
    Route::post('blockSchedules', 'BlockScheduleController@store');
    Route::post('blockSchedules/bulkCreate', 'BlockScheduleController@bulkCreate');
    Route::delete('blockSchedules/{block_schedule_id}', 'BlockScheduleController@destroy');
    
    //App
    Route::get('apps', 'AppController@index');
    Route::post('apps', 'AppController@store');
    Route::put('apps', 'AppController@update');
    Route::put('apps/changeStatus', 'AppController@changeStatus');
    
    //Mail
    Route::get('sendmail', 'MailController@index');
    //Route::get('apps', 'MailController@index');
});

Route::any('{all}', function(){
    return 'API Agenda de Citas';
})->where('all', '.*');