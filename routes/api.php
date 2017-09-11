<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('auth/app', 'Auth\AppAuthController@authenticateApp');

Route::group(['prefix' => 'parser', 'namespace' => 'Parser'], function () {
    Route::get('current_version', 'ParserController@getVersion')
        ->name('parser.version')
    ->middleware('auth.api');

    Route::post('get_resource', 'ParserController@getResource')
        ->name('parser.resource')
        ->middleware('auth.api');
});

