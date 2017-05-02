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
			$activeds = isset($r->activeds) ? $r->activeds : false;

			$Types = BannersTypes::query();

			if ($activeds) {
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

			$data = [];

			foreach($Types->get() as $k => $t) {
				$data[$k] = $t;
				$data[$k]->status_text = self::getStatusText($t->status);
				$data[$k]->total_banners = $t->banners()->count();
			}

			return $success($data);
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

	/**
	 * @param $r
	 * @param \Closure $success
	 * @param \Closure $error
	 * @return mixed
	 */
	public static function viewById($r, \Closure $success, \Closure $error) {
		try {
			$bt = BannersTypes::where('id', '=', $r->type);

			return $success($bt->get());
		} catch (Exception $e) {
			return $error($e);
		}
	}

	/**
	 * @param $r
	 * @param \Closure $success
	 * @param \Closure $error
	 * @return mixed
	 */
	public static function listBannersByManyTypes ($r, \Closure $success, \Closure $error) {
		try {
			$types = BannersTypes::query();
			$types = $types->whereIn('id', $r->types)->get();

			$data = [];

			foreach($types as $t) {
				$banners = $t->banners();
				$dataBanners = Banners::getBanners($banners, $r);
				foreach(self::mergeBanners($data, $dataBanners) as $b) {
					array_push($data, $b);
				}
			}

			return $success($data);
		} catch (\Exception $e) {
			return $error($e);
		}
	}

	/**
	 * @param $data1
	 * @param $data2
	 * @return array
	 */
	protected static function mergeBanners($data1, $data2) {
		$data=[];
		if(count($data1) > 0 && count($data2) > 0) {
			foreach ($data2 as $d2) {
				$diff=true;
				foreach ($data1 as $d1) {
					if ($d1->id == $d2->id) {
						$diff=false;
					}
				}
				if($diff)
					array_push($data, $d2);
			}
		} else {
			foreach ($data2 as $d2) {
				array_push($data, $d2);
			}
		}

		return $data;
	}

	/**
	 * @param $s
	 * @return string
	 */
	public static function getStatusText ($s) {
		switch ($s) {
			case self::STATUS_TRUE :
				return 'Ativado';
				break;
			case self::STATUS_FALSE :
				return 'Desativado';
				break;
			default :
				return 'Indefinido';
		}
	}
}
