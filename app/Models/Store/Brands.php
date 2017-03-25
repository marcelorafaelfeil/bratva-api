<?php

namespace App\Models\Store;

use App\Models\Generic\FriendlyUrl;
use Illuminate\Database\Eloquent\Model;

class Brands extends Model
{
	const CREATED_AT = 'created_at';
	const UPDATED_AT = 'updated_at';

	const STATUS_TRUE = 1;
	const STATUS_FALSE = 0;

	protected $table = 'brands';
	protected $fillable = [
		'id',
		'name',
		'description',
		'status',
		'friendly_url_id',
		'safe_delete'
	];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function products () {
		return $this->hasMany('App\Models\Store\Products');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function images () {
		return $this->belongsToMany('App\Models\Generic\Images', 'brands_has_images', 'brands_id', 'images_id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function url () {
		return $this->hasOne('App\Models\Generic\FriendlyUrl', 'id', 'friendly_url_id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function newQuery () {
		$query = parent::newQuery();
		$query->where('safe_delete', '=', 0);
		return $query;
	}

	/**
	 * @param $id
	 * @return bool
	 */
	public static function has ($id) {
		$b = Brands::find($id);
		return !!($b);
	}

	public static function hasUrl($url) {
		$table = (new FriendlyUrl())->getTable();
		$b = Brands::whereHas('url', function($query) use ($url, $table) {
			$query->where($table . '.url', '=', $url);
		})
			-> count();

		return ($b > 0);
	}

	/**
	 * @param $b
	 * @param $i
	 * @return mixed
	 */
	public static function relationImages ($b, $i) {
		return Brands::find($b)->images()->attach($i);
	}

	/**
	 * @param $r
	 * @param \Closure $success
	 * @param \Closure $error
	 * @return mixed
	 */
	public static function add ($r, \Closure $success, \Closure $error) {
		try {
			$u = FriendlyUrl::create(['url' => $r->url]);
			$b = Brands::create([
				'name' => $r->name,
				'description' => $r->description,
				'status' => $r->status,
				'friendly_url_id' => $u->id
			]);

			return $success($b);
		} catch (\Exception $e) {
			return $error($e);
		}
	}

	/**
	 * @param $r
	 * @param \Closure $success
	 * @param \Closure $error
	 * @return mixed
	 */
	public static function edit ($r, \Closure $success, \Closure $error) {
		try {
			$b = Brands::find($r->id);
			$b->name = $r->name;
			$b->description = $r->description;
			$b->status = $r->status;

			$u = $b->url;
			$u->url = $r->url;

			$u->save();
			$b->save();

			return $success($b);
		} catch (\Exception $e) {
			return $error($e);
		}
	}

	/**
	 * @param $brand
	 * @param \Closure $success
	 * @param \Closure $error
	 * @return mixed
	 */
	public static function remove ($brands, \Closure $success, \Closure $error) {
		try {
			$whereIn=[];
			foreach($brands as $b) {
				array_push($whereIn, $b);
			}

			$brands = Brands::whereIn('id', $whereIn)->get();
			Brands::whereIn('id', $whereIn)->update(['safe_delete' => 1]);

			return $success($brands);
		} catch (\Exception $e) {
			return $error($e);
		}
	}

	/**
	 * @param $r
	 * @param \Closure $success
	 * @param \Closure $error
	 * @return mixed
	 */
	public static function lists ($r, \Closure $success, \Closure $error) {
		try {
			$where=[];
			$order_column = 'id';
			$order_by = 'DESC';
			$limit = 100;
			$page = 0;

			if(isset($r->status))
				$where[] = ['status', '=', $r->status];
			if(isset($r->order_column))
				$order_column = $r->order_column;
			if(isset($r->order_by))
				$order_by = $r->order_by;
			if(isset($r->limit))
				$limit = $r->limit;
			if(isset($r->page))
				$page = $r->page;

			$skip = $limit * $page;

			$brands = Brands::where($where);

			$brands->orderBy($order_column, $order_by);

			$brands = $brands
				->select([
					'id',
					'name',
					'description',
					'friendly_url_id'
				])
				-> skip($skip)
				-> take($limit);

			$brands = $brands->get();

			$data = [];
			foreach($brands as $k => $b) {
				$url='';
				$thumb='';

				$images = $b->images()->orderBy('featured', 'DESC');
				if($url = isset($b->url->url))
					$url = $b->url->url;
				if($images = $images->first())
					$thumb = $images->src;

				$data[] = [
					'id' => $b->id,
					'name' => $b->name,
					'description' => $b->description,
					'url' => $url,
					'thumb' => $thumb
				];
			}
			return $success($data);
		} catch (\Exception $e) {
			return $error($e);
		}
	}

	public static function listProducts($r, \Closure $success, \Closure $error) {
		try {
			if($r->brand_id)
				$brand = Brands::find($r->brand_id);
			if($r->brand_url)
				$brand = FriendlyUrl::where('url', '=', $r->brand_url)->first()->brand()->first();

			$Products = $brand->products();

			$data = Products::getListProducts($Products, $r);

			return $success($data);
		} catch (\Exception $e) {
			return $error($e);
		}
	}
}