<?php

/**
 * User: Marcelo Rafael <marcelo.rafael.feil@gmail.com>
 * Date: 16/04/2017
 */

namespace App\Models\Website;

use App\Models\Generic\Emails;
use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model {
	const CREATED_AT = 'created_at';
	const UPDATED_AT = 'updated_at';

	protected $table = 'newsletter';

	protected $fillable = [
		'emails_id',
		'safe_delete'
	];
	protected $hidden = [
		'safe_delete'
	];

	/**
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function newQuery () {
		$query = parent::newQuery();
		$query->where('safe_delete', '=', 0);
		return $query;
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function email() {
		return $this->hasOne('App\Models\Generic\Emails', 'id', 'emails_id');
	}

	/**
	 * @param $e
	 * @param string $id
	 * @return bool
	 */
	public static function hasEmail($e, $id='') {
		$newsletter = Newsletter::query();
		if(!empty($id)) {
			$newsletter->where('id', '!=', $id);
		}
		$exists = false;
		foreach($newsletter->get() as $n) {
			if($n->email->email == $e) {
				$exists=true;
			}
		}
		return $exists;
	}

	/**
	 * @param $n
	 * @return bool
	 */
	public static function has($n) {
		return !!(Newsletter::find($n));
	}

	/**
	 * @param $r
	 * @param \Closure $success
	 * @param \Closure $error
	 * @return mixed
	 */
	public static function add($r, \Closure $success, \Closure $error) {
		try {
			if(isset($r->email) && !empty($r->email)) {
				if(!Emails::has($r->email)) {;
					$e = Emails::create(['email' => $r->email]);
				} else
					$e = Emails::where('email', '=', $r->email)->first();
			}
			$news = Newsletter::create([
				'emails_id' => $e->id
			]);

			return $success($news);
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
			$news = Newsletter::find($r->id);
			$news->emails_id = $r->email;
			$news->save();

			return $success($news);
		} catch (\Exception $e) {
			return $error($e);
		}
	}

	/**
	 * @param \Closure $success
	 * @param \Closure $error
	 * @return mixed
	 */
	public static function lists(\Closure $success, \Closure $error) {
		try {
			$newsletter = Newsletter::query();
			$data=[];
			foreach($newsletter->get() as $n) {
				$n->email;
				array_push($data, $n);
			}

			return $success($data);
		} catch(\Exception $e) {
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
			$news = Newsletter::whereIn('id', $r->newsletters);
			$data = $news->get();
			$news->update(['safe_delete' => 1]);
			return $success($data);
		} catch (\Exception $e) {
			return $error($e);
		}
	}
}
