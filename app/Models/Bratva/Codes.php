<?php

namespace App\Models\Bratva;

use Illuminate\Database\Eloquent\Model;

class Codes extends Model {
	const CREATED_AT = 'created_at';
	const UPDATED_AT = 'updated_at';

	protected $primaryKey = 'code';
	public $incrementing = false;
    protected $table = 'codes';
    protected $fillable = [
    	'code',
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
	 * @param $code
	 * @return bool
	 */
    public static function has($code) {
    	return !!(Codes::find($code));
    }

	/**
	 * @param $r
	 * @param \Closure $success
	 * @param \Closure $error
	 * @return mixed
	 */
    public static function add($r, \Closure $success, \Closure $error) {
    	try {
		    $codes = Codes::create([
		    	'code' => $r->code
		    ]);

		    return $success($codes);
	    } catch (\Exception $e) {
    		return $error($e);
	    }
    }

	/**
	 * @param $codes
	 * @param \Closure $success
	 * @param \Closure $error
	 * @return mixed
	 */
    public static function remove($codes, \Closure $success, \Closure $error) {
	    try {
	    	$whereIn = [];
		    foreach($codes as $c) {
		    	array_push($whereIn, $c);
		    }
		    $codes = Codes::whereIn('code', $whereIn);
		    $data = $codes->get();

		    $codes->update(['safe_delete' => 1]);

		    return $success($data);
	    } catch (\Exception $e) {
		    return  $error($e);
	    }
    }
}
