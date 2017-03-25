<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;

class BannersTypes extends Model {
	const CREATED_AT = 'created_at';
	const UPDATE_AT = 'updated_at';

	const STATUS_FALSE = 0;
	const STATUS_TRUE = 1;

	const EXPIRE_FALSE = 0;
	const EXPIRE_TRUE = 1;

	protected $table = 'banners_types';
	protected $fillable = [
		'id',
		'title',
		'description',
		'status',
		'order',
		'expire',
		'date_start',
		'date_end',
		'safe_delete'
	];
	protected $hidden = [
		'safe_delete'
	];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function banners () {
		return $this->belongsToMany('App\Models\Website\Banners', 'banners_types_has_banners', 'banners_types_id','banners_id');
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
	public static function has ($code) {
		$bt = BannersTypes::find($code);
		return !!($bt);
	}

	/**
	 * @param $r
	 * @param \Closure $success
	 * @param \Closure $error
	 * @return mixed
	 */
	public static function add ($r, \Closure $success, \Closure $error) {
		try {
			$tb = BannersTypes::create([
				'title' => $r->title,
				'description' => $r->description,
				'status' => $r->status,
				'order' => $r->order,
				'expire' => $r->expire,
				'date_start' => $r->date_start,
				'date_end' => $r->date_end
			]);

			return $success($tb);
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
			$tb = BannersTypes::find($r->id);
			$tb->title = $r->title;
			$tb->description = $r->description;
			$tb->status = $r->status;
			$tb->order = $r->order;
			$tb->expire = $r->expire;
			$tb->date_start = $r->date_start;
			$tb->date_end = $r->date_end;

			$tb->save();

			return $success($tb);
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
			$Banners = BannersTypes::whereIn('id', $r->types);
			$tbs = $Banners->get();
			$Banners->update([
				'safe_delete' => 1
			]);

			return $success($tbs);
		} catch (\Exception $e) {
			return $error($e);
		}
	}

	/**
	 * @param $r
	 * @param string $success
	 * @param string $error
	 * @return mixed
	 */
	public static function lists ($r, $success = '', $error = '') {
		try {
			$status = isset($r->status) ? $r->status : 1;
			$orderBy = isset($r->orderBy) ? $r->orderBy : 'ASC';
			$orderColumn = isset($r->orderColumn) ? $r->orderColumn : 'order';
			$limit = isset($r->limit) ? $r->limit : null;
			$page = isset($r->page) ? $r->page : null;

			$Types = BannersTypes::query();

			if ($r->activeds) {
				$Types->where([
					['status', '=', $status],
					['expire', '=', 1],
					['date_start', '<=', date('Y-m-d H:i:s')],
					['date_end', '>=', date('Y-m-d H:i:s')]
				]);
				$Types->orWhere([
					['status', '=', $status],
					['expire', '=', 0],
				]);
			}

			$Types->orderBy($orderColumn, $orderBy);

			if ($limit) {
				$Types->take($limit);
				if ($page) {
					$skip = $limit * $page;
					$Types->skip($skip);
				}
			}

			return $success($Types->get());
		} catch (\Exception $e) {
			return $error($e);
		}
	}

	public static function listBanners ($r, \Closure $success, \Closure $error) {
		try {
			$Types = BannersTypes::find($r->type);
			$Banners = $Types->banners();
			$data = Banners::getBanners($Banners, $r);

			return $success($data);
		} catch (\Exception $e) {
			return $error($e);
		}
	}
}
