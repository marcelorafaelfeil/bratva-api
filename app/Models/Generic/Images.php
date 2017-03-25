<?php

namespace App\Models\Generic;

use Illuminate\Database\Eloquent\Model;
use App\Models\Store;
use App\Models\Website;

class Images extends Model
{
	const FEATURED_TRUE = 1;
	const FEATURED_FALSE = 0;

    protected $table = 'images';
	protected $fillable = [
		'src',
		'legend',
		'featured',
		'safe_delete'
	];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function product() {
		return $this->belongsToMany('App\Models\Store\Products', 'products_has_images', 'products_id', 'images_id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function brand() {
		return $this->belongsToMany('App\Models\Store\Brands', 'brands_has_images', 'brands_id', 'images_id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function newQuery() {
		$query = parent::newQuery();
		$query->where($this->getTable().'.safe_delete', '=', 0);
		return $query;
	}

	/**
	 * @param $image
	 * @return bool
	 */
	public static function has($image) {
		$i = Images::find($image);
		return !!($i);
	}

	/**
	 * @param $s
	 * @param $k
	 * @param $i
	 */
	public static function createRelation($s, $k, $i) {
		switch($s) {
			case 'products':
				Store\Products::relationImages($k, $i);
				break;
			case 'brands':
				Store\Brands::relationImages($k, $i);
				break;
			case 'banners':
				Website\Banners::relationImages($k, $i);
				break;
		}
	}

	/**
	 * @param $image
	 * @param \Closure $success
	 * @param \Closure $error
	 * @return mixed
	 */
	public static function remove($image, \Closure $success, \Closure $error) {
		try {
			$i = Images::find($image);
			$i->safe_delete = 1;
			$i->save();
			return $success($i);
		} catch (\Exception $e) {
			return $error($e);
		}
	}
}
