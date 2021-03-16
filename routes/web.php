<?php

use Illuminate\Support\Facades\Auth;
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
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::middleware('auth')->group(function(){
    Route::get('admin/dashboard', 'DashboardController@show');
Route::get('admin/user/list', 'AdminUserController@list');
Route::get('admin/user/add', 'AdminUserController@add');
Route::post('admin/user/store', 'AdminUserController@store');
Route::get('admin/user/delete/{id}', 'AdminUserController@delete')->name('delete_user');
Route::get('admin/user/action', 'AdminUserController@action');
Route::get('admin/user/edit/{id}', 'AdminUserController@edit')->name('admin/edit');
Route::post('admin/user/update/{id}', 'AdminUserController@update')->name('admin/update');
});

