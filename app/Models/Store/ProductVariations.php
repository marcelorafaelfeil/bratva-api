<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Model;

class ProductVariations extends Model
{
    protected $table = 'product_variations';
	protected $fillable = [
		'name',
		'code',
		'safe_delete'
	];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function products() {
		return $this->hasMany('App\Models\Store\ProductVariationsValues');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function father() {
		return $this->hasOne('App\Models\Store\Products');
	}
}
