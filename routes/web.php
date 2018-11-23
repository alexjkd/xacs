<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('/tr069','ACSController@CpeLogin');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::prefix('cpe')->group(function() {
    Route::get('/acs', 'Auth\CpeLoginController@connection_request');
});



Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
