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

Route::get('images/{class}/{key}/{filename}', function ($class, $key, $filename) {
	$kd = str_split($key);
	$keydir="";
	$i=0;
	foreach($kd as $k) {
		$i++;
		$keydir.=$k;
		if(count($kd) > $i) {
			$keydir.='/';
		}
	}
	$path = storage_path() . '/' . $class. '/'. $keydir . '/' . $filename;

	if(file_exists($path)) {
		return Image::make($path)->response();
	} else {
		$path = storage_path() . '/uploads/noimg.jpg';
		return Image::make($path)->response();
	}
});