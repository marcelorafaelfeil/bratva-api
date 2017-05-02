<?php

namespace App\Models\Website;

use App\Libraries\Utils;
use App\Models\Generic\FriendlyUrl;
use App\Models\Generic\Images;
use Illuminate\Database\Eloquent\Model;

class Pages extends Model {
	const CREATED_AT = 'created_at';
	const UPDATED_AT = 'updated_at';

	const EXPIRE_FALSE = 0;
	const EXPIRE_TRUE = 1;

	const STATUS_FALSE = 0;
	const STATUS_TRUE = 1;

	protected $fillable = [
		'title',
		'content',
		'short_description',
		'long_description',
		'status',
		'expire',
		'date_start',
		'date_end',
		'safe_date',
		'friendly_url_id'
	];
	protected $hidden = [
		'safe_delete'
	];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function url () {
		return $this->hasOne('App\Models\Generic\FriendlyUrl', 'id', 'friendly_url_id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function images() {
		return $this->belongsToMany('App\Models\Generic\Images', 'pages_has_images', 'pages_id', 'images_id');
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
		return !!(Pages::find($code));
	}

	/**
	 * @param $url
	 * @return bool
	 */
	public static function hasByUrl($url) {
		return (FriendlyUrl::where('url', '=', $url)->count()>0);
	}

	/**
	 * @param $r
	 * @param \Closure $success
	 * @param \Closure $error
	 * @return mixed
	 */
	public static function add ($r, \Closure $success, \Closure $error) {
		try {
			$status = isset($r->status) ? $r->status : 1;
			$u = FriendlyUrl::create(['url' => $r->url]);
			$p = Pages::create([
				'title' => $r->title,
				'content' => $r->content,
				'short_description' => $r->short_description,
				'long_description' => $r->long_description,
				'status' => $status,
				'expire' => $r->expire,
				'date_start' => $r->date_start,
				'date_end' => $r->date_end,
				'friendly_url_id' => $u->id
			]);
			$p->url;

			return $success($p);
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
			$p = Pages::find($r->id);
			$p->title = $r->title;
			$p->content = $r->content;
			$p->short_description = $r->short_description;
			$p->long_description = $r->long_description;
			$p->status = $r->status;
			$p->expire = $r->expire;
			$p->date_start = $r->date_start;
			$p->date_end = $r->date_end;

			$u = $p->url;
			$u->url = $r->url;
			$u->save();

			$p->save();

			return $success($p);
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
			$Pages = Pages::whereIn('id', $r->pages);
			$data = $Pages->get();
			$Pages->update(['safe_delete' => 1]);

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
	public static function lists ($r, \Closure $success, \Closure $error) {
		try {
			$orderBy = isset($r->orderBy) ? $r->orderBy : 'ASC';
			$orderColumn = isset($r->orderColumn) ? $r->orderColumn : 'id';
			$limit = isset($r->limit) ? $r->limit : null;
			$page = isset($r->page) ? $r->page : null;
			$status = isset($r->status) ? $r->status : null;

			$Pages = Pages::query();

			if ($r->activeds) {
				if(isset($r->status)) {
					$Pages->where([
						['status', '=', $status],
						['expire', '=', 1],
						['date_start', '<=', date('Y-m-d H:i:s')],
						['date_end', '>=', date('Y-m-d H:i:s')]
					]);
					$Pages->orWhere([
						['status', '=', $status],
						['expire', '=', 0],
					]);
				} else {
					$Pages->where([
						['expire', '=', 1],
						['date_start', '<=', date('Y-m-d H:i:s')],
						['date_end', '>=', date('Y-m-d H:i:s')]
					]);
					$Pages->orWhere([
						['expire', '=', 0]
					]);
				}
			} else {
				if(isset($r->status)) {
					$Pages->where('status', '=', $status);
				}
			}

			$Pages->orderBy($orderColumn, $orderBy);

			if ($limit) {
				$Pages->take($limit);
				if ($page) {
					$page = $limit * $page;
					$Pages->skip($page);
				}
			}

			$Pages->select(['id', 'title', 'short_description', 'status', 'long_description', 'created_at', 'lock']);
			$data = $Pages->get();
			foreach($data as $k => $d) {
				$data[$k]->lock= (int)$d->lock;
				$data[$k]->status = (int)$d->status;
				$data[$k]->status_text = self::getStatusText($d->status);
			}

			return $success($data);
		} catch (\Exception $e) {
			return $error($e);
		}
	}

	/**
	 * @param $code
	 * @param \Closure $success
	 * @param \Closure $error
	 * @return mixed
	 */
	public static function view ($code, \Closure $success, \Closure $error) {
		try {
			$p = Pages::find($code);
			$p->images = $p->images()->orderBy('featured', 'DESC')->get();

			foreach ($p->images as $i => $img) {
				$p->images[$i]->base64 = Utils::ConvertBlobToBase64($img, Utils::KeyDir($p->id), 'pages');
				$p->images[$i]->featured = (int)$p->images[$i]->featured;
			}

			$data = [
				'id' => $p->id,
				'title' => $p->title,
				'content' => $p->content,
				'short_description' => $p->short_description,
				'long_description' => $p->long_description,
				'status' => (int)$p->status,
				'expire' => $p->expire,
				'date_start' => $p->date_start,
				'date_end' => $p->date_end,
				'images' => $p->images
			];

			if($p->url) {
				$data['url'] = $p->url->url;
			} else {
				$data['url'] = '';
			}
			return $success($data);
		} catch (\Exception $e) {
			return $error($e);
		}
	}

	/**
	 * @param $url
	 * @param \Closure $success
	 * @param \Closure $error
	 * @return mixed
	 */
	public static function viewByUrl ($url, \Closure $success, \Closure $error) {
		try {
			$u = FriendlyUrl::where('url', '=', $url)->first();
			$p = $u->pages()->first();
			$p->url;
			return $success($p);
		} catch (\Exception $e) {
			return $error($e);
		}
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

	/**
	 * @param $p
	 * @param $i
	 * @return mixed
	 */
	public static function relationImages ($p, $i) {
		return Pages::find($p)->images()->attach($i);
	}

	/**
	 * @param $r
	 * @param \Closure $success
	 * @param \Closure $error
	 * @return mixed
	 */
	public static function featuredImage ($r, \Closure $success, \Closure $error) {
		try {
			// Muda todas as imagens para featured = 0
			$p = Pages::find($r->page);
			$images = $p->images();
			$images->update(['featured' => 0]);
			$img = Images::find($r->image);
			$img->featured = 1;
			$img->save();

			return $success($img);
		} catch (\Exception $e) {
			return $error($e);
		}
	}

	/**
	 * @param $page
	 * @param $image
	 * @return bool|int
	 */
	public static function hasImage($page, $image) {
		$p = Pages::find($page);
		if($p) {
			$img = $p->images();
			$img->where('id', '=', $image);
			return ($img->count() > 0);
		} else {
			return 0;
		}
	}
}