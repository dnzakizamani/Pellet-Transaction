<?php
Route::group(['namespace' => 'Trs\Local', 'middleware' => ['web', 'auth']], function () {
	Route::resource('/trs_local_npelet', 'NpeletController');
	Route::get('/trs_local_npelet_list', 'NpeletController@getList');
	Route::get('/trs_local_npelet_lookup', 'NpeletController@getLookup');
	Route::get('/trs_local_npelet_getdata', 'NpeletController@getData');
});