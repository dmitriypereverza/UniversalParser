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
Route::post('auth/app', 'Auth\AppAuthController@authenticateApp')
    ->middleware('ipcheck');

Route::group(['prefix' => 'parser', 'namespace' => 'Parser', 'middleware' => ['auth.api', 'ipcheck']], function () {
    Route::get('current_version', 'ParserController@getVersion')
        ->name('parser.version');

    Route::post('get_package_count', 'ParserController@getPackageCount')
        ->name('parser.package.count');

    Route::post('get_connection_id', 'ParserController@getConnectionId')
        ->name('parser.connection.id');

    Route::post('get_connection_info', 'ParserController@getConnectionInfo')
        ->name('parser.connection.info');

    Route::post('get_package_by_number', 'ParserController@getPackageByNumber')
        ->name('parser.package.get');
});

