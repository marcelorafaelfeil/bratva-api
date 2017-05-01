<?php
/**
 * Created by PhpStorm.
 * User: Marcelo Rafael
 * Date: 29/01/2017
 * Time: 03:15
 */

namespace App\Libraries;


class Utils {
	/**
	 * @param $string
	 * @return mixed|string
	 */
	public static function ClearString($string) {
		$letters = array(
			'á'=>'a', 'à'=>'a', 'ã'=>'a', 'â'=>'a', 'ä'=>'a', 'é'=>'e', 'è'=>'e', 'ê'=>'e', 'ë'=>'e', 'í'=>'i',
			'ì'=>'i', 'î'=>'i', 'ï'=>'i', 'ó'=>'o', 'ò'=>'o', 'õ'=>'o', 'ô'=>'o', 'ö'=>'o', 'ú'=>'u', 'ù'=>'u',
			'û'=>'u', 'ü'=>'u', 'ç'=>'ç', 'ñ'=>'n', 'Á'=>'A', 'À'=>'A', 'Ã'=>'A', 'Â'=>'A', 'Ä'=>'A', 'É'=>'A',
			'È'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Í'=>'I', 'Ì'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ó'=>'O', 'Ò'=>'O', 'Õ'=>'O',
			'Ô'=>'O', 'Ö'=>'O', 'Ú'=>'U', 'Ù'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ñ'=>'N', 'ç'=>'c', 'Š'=>'S', 'š'=>'s',
			'Ž'=>'Z', 'ž'=>'z', 'Ø'=>'O', 'ø'=>'o', 'Ý'=>'Y', 'ý'=>'y', 'þ'=>'B', 'ÿ'=>'y', 'Þ'=>'b', 'ß'=>'Ss',
			'ğ'=>'g', 'Ğ'=>'G', 'ş'=>'s', 'Ş'=>'S'
		);
		$string = str_replace(' ','-',$string);
		$string = str_replace('.','',$string);
		$string = strtr($string,$letters);
		$string = preg_replace('~[^-\w.]+~', '', $string);

		return $string;
	}

	/**
	 * @param $key
	 * @return string
	 */
	public static function KeyDir($key) {
		$key = str_split($key);
		$keydir="";
		$i=0;
		foreach($key as $k) {
			$i++;
			$keydir.=$k;
			if(count($key) >= $i) {
				$keydir.='/';
			}
		}

		return $keydir;
	}

	public static function ConvertBlobToBase64($img, $keydir, $module) {
		$name = explode('/', $img->src);
		$name = $name[count($name) - 1];
		$path = storage_path() . '/'.$module.'/' . $keydir . '/' . $name;

		if (file_exists($path)) {
			$base64 = base64_encode(file_get_contents($path));
			return $base64;
		}
		return '';
	}

	/**
	 * @param $date
	 * @return bool
	 */
	public static function ValidateDate($date) {
		try {
			new \DateTime($date);
			return true;
		} catch (\Exception $e) {
			return false;
		}
	}
}