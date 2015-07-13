<?php

/*
|--------------------------------------------------------------------------
| Input Routes
|--------------------------------------------------------------------------
*/

Route::any('api/input/{all?}', 'Tdt\Input\Controllers\InputController@handle')->where('all', '.*');
Route::any('api/encodings', function () {

    // Return the supported character encodings
    return \Response::json(mb_list_encodings());
});
