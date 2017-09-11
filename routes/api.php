<?php

use Illuminate\Http\Request;
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

Route::group(['middleware' => 'api', 'prefix' => 'parser', 'namespace' => 'Parser'], function () {
    Route::get('current_version', 'ParserController@getVersion')->name('parser.version');
    Route::post('get_resource', 'ParserController@getResource')->name('parser.resource');
});
