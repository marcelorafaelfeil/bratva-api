<?php

namespace App\Models\Generic;

use Illuminate\Database\Eloquent\Model;

class Emails extends Model {
	const CREATED_AT = 'created_at';
	const UPDATED_AT = 'updated_at';

	const VERIFIED_TRUE = 1;
	const VERIFIED_FALSE = 0;

	protected $fillable = [
		'email',
		'verified',
		'safe_delete'
	];
	protected $hidden = [
		'safe_delete'
	];

	/**
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function newQuery() {
		$query = parent::newQuery();
		$query->where('safe_delete', '=', 0);
		return $query;
	}

	/**
	 * @param $e
	 * @return bool
	 */
	public static function has($e, $id='') {
		if(!empty($id)) {
			return (Emails::where([
				['id', '!=', $id],
				['email', '=', $e]
			])->count() > 0);
		} else {
			return (Emails::where('email', $e)->count() > 0);
		}
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function newsletter() {
		return $this->hasOne('App\Models\Website\Newsletter', 'emails_id');
	}

	/**
	 * @param $r
	 * @param \Closure $success
	 * @param \Closure $error
	 * @return mixed
	 */
	public static function add($r, \Closure $success, \Closure $error) {
		try {
			$e = Emails::create([
				'email' => $r->email,
				'verified' => Emails::VERIFIED_FALSE
			]);

			return $success($e);
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
			$data = Emails::find($r->id);
			$data->email = $r->email;
			if(isset($r->verified) && $r->verified != "")
				$data->verified = $r->verified;
			$data->save();

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
	public static function remove($r, \Closure $success, \Closure $error) {
		try {
			$emails = Emails::whereIn('id', $r->emails);
			$data = $emails->get();

			$emails->update(['safe_delete' => 1]);

			return $success($data);
		} catch (\Exception $e) {
			return $error($e);
		}
	}

	public static function lists($r, \Closure $success, \Closure $error) {
		try {
			$verified = $r->verified ? $r->verified : null;
			$limit = $r->limit ? $r->limit : null;
			$page = $r->page ? $r->page : null;
			$orderBy = $r->orderBy ? $r->orderBy : 'DESC';
			$orderColumn = $r->orderColumn ? $r->orderColumn : 'id';

			$emails = Emails::query();

			if($verified)
				$emails->where('verified', '=', $r->verified);

			if($limit) {
				$skip = $limit * $page;
				$emails->take($limit);
				if($page) {
					$emails->skip($skip);
				}
			}

			$emails->orderBy($orderColumn, $orderBy);

			$data = $emails->get();

			return $success($data);
		} catch (\Exception $e) {
			return $error($e);
		}
	}
}