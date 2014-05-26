<?php

/*
|--------------------------------------------------------------------------
| Input Routes
|--------------------------------------------------------------------------
*/

Route::any('api/input/{all?}', 'Tdt\Input\Controllers\InputController@handle')->where('all', '.*');