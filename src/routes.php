<?php

/*
|--------------------------------------------------------------------------
| Input Routes
|--------------------------------------------------------------------------
*/

Route::any('api/input/jobs/logs/{identifier?}', 'Tdt\Input\Controllers\LogController@get')->where('identifier', ('.+'));

Route::any('api/input/{all?}', 'Tdt\Input\Controllers\InputController@handle')->where('all', '.*');
