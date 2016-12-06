<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::group(['as' => 'public::'], function () {
    Route::get('/', ['as' => 'home', 'uses' =>'TasksController@index']);
    Route::post('task/createtask', ['as' => 'create.task', 'uses' =>'TasksController@createTask']);
    Route::post('task/updatetask', ['as' => 'update.task', 'uses' =>'TasksController@updateTask']);
    Route::post('task/completed', ['as' => 'set.task.completed', 'uses' =>'TasksController@setTaskToCompleted']);
    Route::post('task/progress', ['as' => 'set.task.progress', 'uses' =>'TasksController@setTaskToProgress']);
});
