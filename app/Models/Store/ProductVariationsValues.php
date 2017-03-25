<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Model;

class ProductVariationsValues extends Model
{
    protected $table = 'product_variations_values';
	protected $fillable = [
		'name',
		'code',
		'quantity',
		'status',
		'safe_delete'
	];


	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function variation() {
		return $this->hasOne('App\Models\Store\Variations');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function father() {
		return $this->hasOne('App\Modes\Store\Products', 'product_variations_products_id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function images() {
		return $this->belongsToMany(
			'App\Models\Generic\Images',
			'product_variations_values_has_images'
		);
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function prices() {
		return $this->belongsTo(
			'App\Models\Store\Prices',
			'product_variations_values_has_prices'
		);
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function sales_info() {
		return $this->belongsToMany(
			'App\Models\Store\SalesInfo',
			'products_has_products_sales_info'
		);
	}
}
