<?php

namespace App\Http\Controllers\Generic;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Libraries\Utils;
use League\Flysystem\Exception;

class UploadsController extends Controller
{
	private $save_original_name = false;

	private $directory;

	protected function setDirectory($d) {
		$this->directory = $d;
	}

	/**
	 * @param $f
	 * @return string
	 */
	private function getFilename($f) {
		$ext = $f->getClientOriginalExtension();
		if(!$this->save_original_name)
			$fileName = mt_rand(9,9999).date('YmdHis').'.'.$ext;
		else {
			$fileName = $f->getClientOriginalName().'.'.$ext;
		}

		return $fileName;
	}

    protected function Upload($f, \Closure $fun, \Closure $error) {
	    try {
		    $fileName = strtolower($this->getFileName($f));
		    if($f->move($this->directory, $fileName)) {
		    	return $fun($fileName);
		    }
	    } catch (\Exception $e) {
	    	return $error($e);
	    }
    }
}
