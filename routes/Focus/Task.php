<?php
/**
 * task
 *
 */
    Route::group( ['namespace' => 'project'], function () {
        Route::post('tasks/load', 'TasksController@load')->name('tasks.load');
        Route::post('tasks/update_status', 'TasksController@update_status')->name('tasks.update_status');
        Route::post('tasks/update_task_completion', 'TasksController@update_task_completion')->name('tasks.update_task_completion');
        Route::resource('tasks', 'TasksController');
        //For Datatable
        Route::post('tasks/get', 'TasksTableController')->name('tasks.get');
    });
