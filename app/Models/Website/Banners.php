<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;

class Banners extends Model {
	const CREATED_AT = 'created_at';
	const UPDATED_AT = 'updated_at';

	const STATUS_FALSE = 0;
	const STATUS_TRUE = 1;

	const EXPIRE_FALSE = 0;
	const EXPIRE_TRUE = 1;

	const TARGET_SELF = 0;
	const TARGET_BLANK = 1;

	protected $table = 'banners';
	protected $fillable = [
		'id',
		'title',
		'legend',
		'link',
		'expire',
		'date_start',
		'date_end',
		'order',
		'target',
		'status',
		'safe_delete'
	];
	protected $hidden = [
		'safe_delete',
		'images_id'
	];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function image () {
		return $this->hasOne('App\Models\Generic\Images', 'id', 'images_id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function types () {
		return $this->belongsToMany('App\Models\Website\BannersTypes', 'banners_types_has_banners', 'banners_id', 'banners_types_id');
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
	 * @param $code
	 * @return bool
	 */
	public static function has($code) {
		$b = Banners::find($code);
		return !!($b);
	}

	/**
	 * @param $r
	 * @param \Closure $success
	 * @param \Closure $error
	 * @return mixed
	 */
	public static function add ($r, \Closure $success, \Closure $error) {
		try {
			$b = Banners::create([
				'title' => $r->title,
				'legend' => $r->legend,
				'link' => $r->link,
				'expire' => $r->expire,
				'date_start' => $r->date_start,
				'date_end' => $r->date_end,
				'order' => $r->order,
				'target' => $r->target,
				'status' => $r->status
			]);

			if ($r->types)
				Banners::relationWithTypes($b->id, $r->types);

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
			$b = Banners::find($r->id);
			$b->title = $r->title;
			$b->legend = $r->legend;
			$b->link = $r->link;
			$b->expire = $r->expire;
			$b->date_start = $r->date_start;
			$b->date_end = $r->date_end;
			$b->order = $r->order;
			$b->target = $r->target;
			$b->status = $r->status;

			$b->save();

			if($r->types)
				Banners::relationWithTypes($r->id, $r->types);

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
	public static function remove ($r, \Closure $success, \Closure $error) {
		try {
			$whereIn = [];
			foreach($r->banners as $b) {
				array_push($whereIn, $b);
			}
			$banners = Banners::whereIn('id', $whereIn)->get();

			Banners::whereIn('id', $whereIn)->update(['safe_delete' => 1]);

			return $success($banners);
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
			$Banners = Banners::query();
			$banners = self::getBanners($Banners, $r);

			return $success($banners);
		} catch (\Exception $e) {
			return $error($e);
		}
	}

	/**
	 * @param $Banners
	 * @param $r
	 * @return mixed
	 */
	public static function getBanners($Banners, $r) {
		$order_by = isset($r->orderBy) ? $r->orderBy : 'ASC';
		$order_column = isset($r->orderColumn) ? $r->orderColumn : 'order';
		$status = isset($r->status) ? $r->status : 1;
		$limit = isset($r->limit) ? $r->limit : null;
		$page = isset($r->page) ? $r->page : null;

		if ($r->activeds) {
			$Banners->where([
				['status', '=', $status],
				['expire', '=', 1],
				['date_start', '<=', date('Y-m-d H:i:s')],
				['date_end', '>=', date('Y-m-d H:i:s')]
			]);
			$Banners->orWhere([
				['status', '=', $status],
				['expire', '=', 0],
			]);
		} else {
			$Banners->where('status', '=', $status);
		}

		$Banners->orderBy($order_column, $order_by);

		if($limit) {
			$Banners->take($limit);

			if($page) {
				$page = $limit * $page;
				$Banners->skip($page);
			}
		}

		$data = [];
		$banners = $Banners->get();

		foreach($banners as $b) {
			$b->image;
			$types = $b->types()->get();
			$b->types = $types;
			array_push($data, $b);
		}

		return $banners;
	}

	/**
	 * @param $banner
	 * @param $image
	 * @return mixed
	 */
	public static function relationImages ($banner, $image) {
		$b = Banners::find($banner);
		$b->images_id = $image->id;
		return $b->save();
	}

	/**
	 * @param $banner
	 * @param $types
	 * @throws \Exception
	 */
	public static function relationWithTypes ($banner, $types) {
		try {
			$Banners = Banners::find($banner);
			$Banners->types()->detach();
			foreach ($types as $t) {
				$Banners->types()->attach($t);
			}
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), 500, $e);
		}
	}
}
