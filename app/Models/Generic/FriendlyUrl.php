<?php

namespace App\Models\Generic;

use App\Models\Store\Categories;
use App\Models\Store\Products;
use App\Models\Store\Brands;
use App\Models\Website\Pages;
use App\Models\Website\PagesCategories;
use Illuminate\Database\Eloquent\Model;

class FriendlyUrl extends Model
{
    const UPDATED_AT = 'updated_at';

	protected $table = 'friendly_url';
	protected $fillable = [
		'url'
	];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function category() {
		return $this->hasOne('App\Models\Store\Categories');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function product() {
		return $this->hasOne('App\Models\Store\Products');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function brand() {
		return $this->hasOne('App\Models\Store\Brands');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function pages() {
		return $this->hasOne('App\Models\Website\Pages');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function pagesCategories() {
		return $this->hasOne('App\Models\Website\PagesCategories');
	}


	/**
	 * @param $u
	 * @param string $section
	 * @param string $key
	 * @return bool
	 */
	public static function has($u, $section='', $key='') {
		$where=[];
		$where[] = ['url', '=', $u];

		if($section && $key) {
			switch ($section) {
				case 'products' :
					$p = Products::find($key);
					$where[] = ['id', '!=', $p->friendly_url_id];
					break;
				case 'brands' :
					$b = Brands::find($key);
					$where[] = ['id', '!=', $b->friendly_url_id];
					break;
				case 'categories' :
					$c = Categories::find($key);
					$where[] = ['id', '!=', $c->friendly_url_id];
					break;
				case 'pages' :
					$p = Pages::find($key);
					$where[] = ['id', '!=', $p->friendly_url_id];
					break;
				case 'pages_categories' :
					$pc = PagesCategories::find($key);
					$where[] = ['id', '!=', $pc->friendly_url_id];
					break;
			}
		}

		$fu = FriendlyUrl::where($where);
		$total = $fu->count();

		/*
		 * Essa verificação por enquanto fica em standy
		 * até se decidir a viabilidade dela.
		 * if($total > 0) {
			$fu = $fu->first();

			if ($section && !$key) {
				switch ($section) {
					case 'products' :
						$total = $fu->product ? 1 : 0;
						break;
					case 'brands' :
						$total = $fu->brand ? 1 : 0;
						break;
					case 'categories' :
						$total = $fu->category ? 1 : 0;
						break;
					default :
						$total = 0;
				}
			}
		}*/
		return ($total > 0);
	}
}
