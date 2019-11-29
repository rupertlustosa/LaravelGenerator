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

Route::prefix('rlustosa/generator')->group(function () {
    Route::get('/', 'LustosaGenerateController@index');
    //Route::get('/', '\Rlustosa\LaravelGenerator\Http\Controllers\LustosaGenerateController@index');
    //Route::get('/{any}', 'LustosaGenerateController@index')->where('any', '.*');
    /*Route::get('/', function () {
        return '=)';
    });*/
});
