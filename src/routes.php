<?php

/*
|--------------------------------------------------------------------------
| Input Routes
|--------------------------------------------------------------------------
*/

Route::any('api/input/{all?}', 'tdt\input\controllers\InputController@handle')->where('all', '.*');