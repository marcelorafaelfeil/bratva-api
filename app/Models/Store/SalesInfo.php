<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Model;

class SalesInfo extends Model
{
	protected $table = 'products_sales_info';
	protected $fillable = [
		'width',
		'height',
		'length',
		'diameter',
		'weight'
	];
	protected $hidden = [
		'pivot'
	];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function products() {
		return $this->belongsToMany('App\Models\Store\Products', 'products_has_products_sales_info');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function productsVariations() {
		return $this->belongsToMany(
			'App\Models\Store\ProductVariationsValues',
			'product_variations_values_has_products_sales_info'
		);
	}
}
