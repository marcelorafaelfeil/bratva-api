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

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function prices() {
		return $this->hasMany('App\Models\Store\Prices');
	}
}
