<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model {
	const CREATED_AT = 'created_at';
	const UPDATED_AT = 'updated_at';

	const STATUS_FALSE = 0;
	const STATUS_TRUE = 1;

	const TYPE_TOP = 0;
	const TYPE_FOOTER = 1;

	const TARGET_SELF = 0;
	const TARGET_BLANK = 1;

	protected $table = 'menu';
	protected $fillable = [
		'type',
		'title',
		'description',
		'link',
		'target',
		'status',
		'safe_delete'
	];
	protected $hidden = [
		'safe_delete'
	];

	public function newQuery () {
		$query = parent::newQuery();
		$query->where('safe_delete', '=', 0);
		return $query;
	}

	public static function has($code) {
		return !!(Menu::find($code));
	}

	/**
	 * @param $r
	 * @param \Closure $success
	 * @param \Closure $error
	 * @return mixed
	 */
	public static function add($r, \Closure $success, \Closure $error) {
		try {
			$status = isset($r->status) ? $r->status : 1;

			$m = Menu::create([
				'type' => $r->type,
				'title' => $r->title,
				'description' => $r->description,
				'link' => $r->link,
				'target' => $r->target,
				'status' => $status
			]);

			return $success($m);
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
	public static function edit($r, \Closure $success, \Closure $error) {
		try {
			$status = isset($r->status) ? $r->status : 1;

			$m = Menu::find($r->id);
			$m->type = $r->type;
			$m->title = $r->title;
			$m->description = $r->description;
			$m->link = $r->link;
			$m->target = $r->target;
			$m->status = $status;
			$m->save();

			return $success($m);
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
	public static function remove($r, \Closure $success, \Closure $error) {
		try {
			$Menu = Menu::whereIn('id', $r->menus);
			$m = $Menu->get();
			$Menu->update(['safe_delete' => 1]);

			return $success($m);
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
	public static function lists($r, \Closure $success, \Closure $error) {
		try {
			$type = isset($r->type) ? $r->type : 0;
			$status = isset($r->status) ? $r->status : null;

			$Menu = Menu::query();

			if($status != null)
				$Menu->where('status', '=', $status);
			$Menu->where('type', '=', $type);
			$Menu->orderBy('order', 'ASC');

			$m = $Menu->get();

			return $success($m);
		} catch (\Exception $e) {
			return $error($e);
		}
	}
}
