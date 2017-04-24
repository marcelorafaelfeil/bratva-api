<?php

namespace App\Models\Store;

use App\Models\Generic\FriendlyUrl;
use App\Models\Generic\Images;
use Illuminate\Database\Eloquent\Model;

class Products extends Model {
	const CREATED_AT = 'created_at';
	const UPDATED_AT = 'updated_at';

	const FEATURED_TRUE = 1;
	const FEATURED_FALSE = 0;
	const STATUS_TRUE = 1;
	const STATUS_FALSE = 0;

	protected $table = 'products';
	protected $fillable = [
		'name',
		'code',
		'quantity',
		'featured',
		'status',
		'short_description',
		'long_description',
		'categories_id',
		'brands_id',
		'friendly_url_id',
		'safe_delete'
	];
	protected $hidden = [
		'friendly_url_id',
		'brands_id',
		'categories_id',
		'safe_delete'
	];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function prices () {
		return $this->belongsToMany('App\Models\Store\Prices', 'products_has_prices', 'products_id', 'prices_id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function sales_info () {
		return $this->belongsToMany(
			'App\Models\Store\SalesInfo',
			'products_has_products_sales_info',
			'products_id',
			'products_sales_info_id'
		);
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function categories () {
		return $this->belongsToMany('App\Models\Store\Categories', 'products_has_categories', 'products_id', 'categories_id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function exactCategory () {
		return $this->hasOne('App\Models\Store\Categories');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function brand () {
		return $this->hasOne('App\Models\Store\Brands', 'id', 'brands_id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function product_variations () {
		return $this->hasMany('App\Models\Store\ProductVariations');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function url () {
		return $this->hasOne('App\Models\Generic\FriendlyUrl', 'id', 'friendly_url_id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function images () {
		return $this->belongsToMany('App\Models\Generic\Images', 'products_has_images', 'products_id', 'images_id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function newQuery () {
		$query = parent::newQuery();
		$query->where($this->getTable() . '.safe_delete', '=', 0);
		return $query;
	}

	/**
	 * @param $c
	 * @return bool
	 */
	public static function hasCode ($c, $product) {
		$where = [];
		$where[] = ['code', '=', $c];

		if ($product) {
			$where[] = ['id', '!=', $product];
		}
		$c = Products::where($where)->count();
		return ($c > 0);
	}

	/**
	 * @param $p
	 * @return bool
	 */
	public static function has ($p) {
		$p = Products::find($p);
		return !!($p);
	}

	/**
	 * @param $r
	 * @param \Closure $f
	 * @param \Closure $error
	 * @return mixed
	 */
	public static function add ($r, \Closure $f, \Closure $error) {
		try {

			$u = FriendlyUrl::create(['url' => $r->url]);

			$prd = [
				'name' => $r->name,
				'code' => $r->code,
				'quantity' => $r->quantity,
				'featured' => $r->featured ? self::FEATURED_TRUE : self::FEATURED_FALSE,
				'status' => $r->status ? self::STATUS_TRUE : self::STATUS_FALSE,
				'short_description' => $r->short_description,
				'long_description' => $r->long_description,
				'categories_id' => $r->category,
				'friendly_url_id' => $u->id
			];

			if ($r->brand > 0)
				$prd['brands_id'] = $r->brand;
			else
				$prd['brands_id'] = NULL;

			$p = Products::create($prd);
			$p->sales_info()
				->create([
					'width' => $r->width,
					'height' => $r->height,
					'length' => $r->length,
					'diameter' => $r->diameter,
					'weight' => $r->weight
				]);

			if (isset($r->prices) && count($r->prices) > 0) {
				for ($i = 0; $i < count($r->prices); $i++) {
					$prc = $r->prices[$i];
					if ($prc['value'] != '') {
						$price = Prices::create([
							'value' => $prc['value'],
							'status' => $prc['status'],
							'default' => $prc['default'],
							'validity_at' => $prc['validity_at'],
							'validity_to' => $prc['validity_to'],
							'currencies_id' => $prc['currencies_id']
						]);

						$p->prices()->attach($price);
					}
				}
			}
			if ($r->categories)
				Products::relationWithCategories($p->id, $r->categories);

			return $f($p);
		} catch (\Exception $e) {
			return $error($e);
		}
	}

	/**
	 * @param $r
	 * @param \Closure $f
	 * @param \Closure $error
	 * @return mixed
	 */
	public static function edit ($r, \Closure $f, \Closure $error) {
		try {
			$p = Products::find($r->id);
			$p->name = $r->name;
			$p->code = $r->code;
			$p->quantity = $r->quantity;
			$p->featured = $r->featured ? self::FEATURED_TRUE : self::FEATURED_FALSE;
			$p->status = $r->status ? self::STATUS_TRUE : self::STATUS_FALSE;
			$p->short_description = $r->short_description;
			$p->long_description = $r->long_description;
			$p->categories_id = $r->category;
			$p->brands_id = ($r->brand == 0) ? NULL : $r->brand;
			$r->categories = isset($r->categories) ? $r->categories : [];


			if(!isset($p->url) || empty($p->url)) {
				$url = FriendlyUrl::create([
					'url' => $p->url
				]);
				$p->friendly_url_id = $p->id;
			} else {
				$u = $p->url;
				$u->url = $r->url;
				$u->save();
			}
			$p->save();

			if($si = $p->sales_info()->first()) {
				$si->width = $r->width;
				$si->height = $r->height;
				$si->length = $r->length;
				$si->diameter = $r->diameter;
				$si->weight = $r->weight;
				$si->save();
			} else {
				$si = SalesInfo::create([
					'width' => $r->width,
					'height' => $r->height,
					'length' => $r->length,
					'diameter' => $r->diameter,
					'weight' => $r->weight
				]);
				$p->sales_info()->attach($si);
			}

			/**** PREÇOS ****/
			// Apaga todos os preços diferente dos que existe na array
			$idPrices = [];
			for ($i = 0; $i < count($r->prices); $i++) {
				if (isset($r->prices[$i]['id']) && !empty($r->prices[$i]['id'])) {
					array_push($idPrices, $r->prices[$i]['id']);
				}
			}

			$prices = $p->prices();
			$prices->whereNotIn('id', $idPrices)->update(['safe_delete' => 1]);

			if (count($r->prices)) {
				for ($i = 0; $i < count($r->prices); $i++) {
					$prc = $r->prices[$i];
					if (isset($prc['id']) && !empty($prc['id'])) {
						$price = Prices::find($prc['id']);
						$price->value = $prc['value'];
						$price->status = $prc['status'];
						$price->default = $prc['default'];
						$price->validity_at = $prc['validity_at'];
						$price->validity_to = $prc['validity_to'];
						$price->currencies_id = $prc['currencies_id'];
						$price->save();
					} else {
						$price = Prices::create([
							'value' => $prc['value'],
							'status' => $prc['status'],
							'default' => $prc['default'],
							'validity_at' => $prc['validity_at'],
							'validity_to' => $prc['validity_to'],
							'currencies_id' => $prc['currencies_id']
						]);

						$p->prices()->attach($price);
					}
				}
			}

			Products::relationWithCategories($r->id, $r->categories);

			$p->prices = $p->prices()->get();
			return $f($p);
		} catch (\Exception $e) {
			return $error($e);
		}
	}

	/**
	 * @param $products
	 * @param \Closure $f
	 * @param \Closure $error
	 * @return mixed
	 */
	public static function remove ($products, \Closure $f, \Closure $error) {
		try {
			$whereIn = [];
			foreach ($products as $d) {
				array_push($whereIn, $d);
			}

			$products = Products::whereIn('id', $whereIn)->get();

			Products::whereIn('id', $whereIn)
				->update(['safe_delete' => 1]);

			return $f($products);
		} catch (\Exception $e) {
			return $error($e);
		}
	}

	/**
	 * @param $r
	 * @param \Closure $f
	 * @param \Closure $error
	 * @return mixed
	 */
	public static function lists ($r, \Closure $f, \Closure $error) {
		try {
			$Products = new Products();
			$data = self::getListProducts($Products, $r);
			return $f($data);
		} catch (\Exception $e) {
			return $error($e);
		}
	}

	/**
	 * @param $Product
	 * @param $r
	 * @return array
	 */
	public static function getListProducts ($Product, $r) {
		$t_prods = (new Products())->getTable();
		$where = [];
		$order_column = 'id';
		$order_by = 'DESC';
		$limit = null;
		$page = null;
		$hasImage = isset($r->hasImage) ? $r->hasImage : null;

		if (isset($r->featured))
			$where[] = [$t_prods . '.featured', '=', $r->featured];
		if (isset($r->status))
			$where[] = [$t_prods . '.status', '=', $r->status];
		if (isset($r->order_column))
			$order_column = $r->order_column;
		if (isset($r->order_by))
			$order_by = $r->order_by;
		if (isset($r->limit))
			$limit = $r->limit;
		if (isset($r->page))
			$page = $r->page;

		$skip = $limit * $page;

		$prds = $Product->where($where);

		$t_price = (new Prices())->getTable();
		if ($order_column == 'prices') {
			$t_fk = $t_prods . '_has_' . $t_price;

			$prds
				->join($t_fk, $t_fk . '.products_id', '=', $t_prods . '.id')
				->join($t_price, $t_price . '.id', '=', $t_fk . '.prices_id')
				->orderBy($t_price . '.value', $order_by);
		} else {
			$prds
				->orderBy($order_column, $order_by);
		}

		$prds = $prds
			->select([
				$t_prods . '.id',
				$t_prods . '.code',
				$t_prods . '.name',
				$t_prods . '.status',
				$t_prods . '.quantity',
				$t_prods . '.short_description',
				$t_prods . '.friendly_url_id',
				$t_prods . '.brands_id'
			]);

		if ($limit) {
			$prds->take($limit);
			if ($page) {
				$prds->skip($skip);
			}
		}

		$products = $prds
			->get();
		$data = [];
		foreach ($products as $p) {
			if (!isset($hasImage) || (($hasImage && $p->images()->count() > 0) || (!$hasImage && $p->images()->count() == 0))) {
				$price = 0.0;
				$image = "";
				$url = "";
				$brand = "";

				$date = new \DateTime();
				$prices = $p
					->prices();

				$prices = $prices->get();
				$price = 0;
				foreach ($prices as $prc) {
					if (
						$prc->validity_at <= $date->format('Y-m-d H:i:s') &&
						$prc->validity_to >= $date->format('Y-m-d H:i:s')
					) {
						$price = $prc->value;
					} else if ($prc->default == Prices::DEFAULT_TRUE) {
						$price = $prc->value;
					}
				}


				$images = $p
					->images()
					->orderBy('featured', 'DESC');
				if ($images = $images->first())
					$image = $images->src;
				if (isset($p->url->url))
					$url = $p->url->url;
				if (isset($p->brand->name))
					$brand = $p->brand;

				$data[] = [
					'id' => $p->id,
					'code' => $p->code,
					'name' => $p->name,
					'quantity' => $p->quantity,
					'status' => $p->status,
					'status_text' => self::getStatusText($p->status),
					'short_description' => $p->short_description,
					'url' => $url,
					'price' => $price,
					'thumb' => $image,
					'brand' => $brand
				];
			}
		}

		return $data;
	}

	/**
	 * @param $key
	 * @param $type
	 * @return mixed
	 */
	private static function getViewProduct ($key, $type) {
		if ($type == 'url')
			$p = FriendlyUrl::where('url', '=', $key)->first()->product;
		else if ($type == 'id')
			$p = Products::find($key);

		$p->brand;
		$p->url;
		$p->images = $p->images()->orderBy('featured', 'DESC')->get();

		$kd = str_split($p->id);
		$i = 0;
		$keydir = '';
		foreach ($kd as $k) {
			$i++;
			$keydir .= $k;
			if (count($kd) > $i) {
				$keydir .= '/';
			}
		}
		foreach ($p->images as $i => $img) {
			$name = explode('/', $img->src);
			$name = $name[count($name) - 1];
			$ext = explode('.', $name);
			$ext = $ext[count($ext) - 1];
			$path = storage_path() . '/products/' . $keydir . '/' . $name;
			if (file_exists($path)) {
				$base64 = base64_encode(file_get_contents($path));
				//$base64 = 'data:image/'.$ext.';base64,'.$base64;
				$p->images[$i]->base64 = $base64;
			}
		}


		if ($type == 'url') {
			$date = new \DateTime();
			$prices = $p
				->prices()
				->where(function ($query) use ($date) {
					$query->where(function ($query) use ($date) {
						$query->where('validity_at', '<=', $date->format('Y-m-d H:i:s'));
						$query->where('validity_to', '>=', $date->format('Y-m-d H:i:s'));
					});
					$query->orWhere(function ($query) use ($date) {
						$query->where('validity_at', '<=', $date->format('Y-m-d H:i:s'));
					});
					$query->orWhere('default', '=', Prices::DEFAULT_TRUE);
				});
			$price = 0;
			if ($prc = $prices->first())
				$price = $prc->value;

			$p->price = $price;
		} else {
			$p->prices = $p->prices()->get();

			$categories = [];
			foreach ($p->categories()->get() as $c) {
				array_push($categories, $c->id);
			}
			$p->categories = $categories;
		}

		return $p;
	}

	/**
	 * @param $url
	 * @param \Closure $success
	 * @param \Closure $error
	 * @return mixed
	 */
	public static function view ($url, \Closure $success, \Closure $error) {
		try {
			return $success(self::getViewProduct($url, 'url'));
		} catch (\Exception $e) {
			return $error($e);
		}
	}

	/**
	 * @param $id
	 * @param \Closure $success
	 * @param \Closure $error
	 * @return mixed
	 */
	public static function viewById ($id, \Closure $success, \Closure $error) {
		try {
			return $success(self::getViewProduct($id, 'id'));
		} catch (\Exception $e) {
			return $error($e);
		}
	}

	/**
	 * @param $p
	 * @param $i
	 * @return mixed
	 */
	public static function relationImages ($p, $i) {
		return Products::find($p)->images()->attach($i);
	}

	/**
	 * @param $product
	 * @param $categories
	 * @throws \Exception
	 */
	public static function relationWithCategories ($product, $categories) {
		try {
			$Product = Products::find($product);
			$Product->categories()->detach();
			if(count($categories) > 0) {
				foreach ($categories as $c) {
					$Product->categories()->attach($c);
				}
			}
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), 500, $e);
		}
	}

	/**
	 * @param $s
	 * @return string
	 */
	public static function getStatusText ($s) {
		switch ($s) {
			case self::STATUS_TRUE :
				return 'Ativado';
				break;
			case self::STATUS_FALSE :
				return 'Desativado';
				break;
			default :
				return 'Indefinido';
		}
	}

	/**
	 * @param $r
	 * @param \Closure $success
	 * @param \Closure $error
	 * @return mixed
	 */
	public static function featuredImage ($r, \Closure $success, \Closure $error) {
		try {
			// Muda todas as imagens para featured = 0
			$p = Products::find($r->product);
			$images = $p->images();
			$images->update(['featured' => 0]);
			$img = Images::find($r->image);
			$img->featured = 1;
			$img->save();

			return $success($img);
		} catch (\Exception $e) {
			return $error($e);
		}
	}

	public static function hasImage($product, $image) {
		$p = Products::find($product);
		if($p) {
			$img = $p->images();
			$img->where('id', '=', $image);
			return ($img->count() > 0);
		} else {
			return 0;
		}
	}
}
