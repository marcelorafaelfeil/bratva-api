<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Model;

class Prices extends Model
{
    const CREATED_AT = 'created_at';
	const UPDATED_AT = 'updated_at';
	const DEFAULT_TRUE = 1;
	const DEFAULT_FALSE = 0;

	protected $table = 'prices';
	protected $fillable = [
		'value',
		'status',
		'default',
		'validity_at',
		'validity_to',
		'currencies_id',
		'safe_delete'
	];
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function currency() {
		return $this->hasOne('App\Models\Store\Currencies');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function products() {
		return $this->belongsToMany('App\Models\Store\Products', 'products_has_prices', 'products_id', 'prices_id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function products_variations() {
		return $this->belongsToMany('App\Models\Store\ProductVarations', 'product_variations_has_prices', 'product_variations_id', 'prices_id');
	}
}
