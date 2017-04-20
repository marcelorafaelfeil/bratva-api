<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Model;

class Currencies extends Model
{

	protected $table = 'currencies';
	protected $fillable = [
		'name',
		'symbol',
		'safe_delete'
	];
	protected $hidden = [
		'safe_delete'
	];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function prices() {
		return $this->hasMany('App\Models\Store\Prices');
	}

	public static function lists($r, \Closure $success, \Closure $error) {
		try {
			$orderBy = isset($r->orderBy) ? $r->orderBy : 'DESC';
			$orderColumn = isset($r->orderColumn) ? $r->orderColumn : 'id';

			$currencies = Currencies::query();
			$currencies->orderBy($orderColumn, $orderBy);
			$dataCurrencies = $currencies->get();

			$data=[];

			foreach($dataCurrencies as $k => $c) {
				$dataCurrencies[$k]->nameSymbol = $c->name.' ('.$c->symbol.')';
				$data[$k] = $dataCurrencies[$k];
			}

			return $success($data);
		} catch (\Exception $e) {
			return $error($e);
		}
	}
}
