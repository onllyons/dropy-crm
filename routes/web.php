<?php

use Illuminate\Support\Facades\Route;

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
    $userCount = \DB::table('users')->count();
    return view('welcome', ['userCount' => $userCount]);
});

Route::get('/models', function () {
    return view('models');
});

Route::get('/datatables', function () {
    return view('datatables');
});
