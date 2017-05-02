<?php

namespace App\Models\Store;

use App\Models\Generic\FriendlyUrl;
use Illuminate\Database\Eloquent\Model;

class Categories extends Model {
	const CREATED_AT = 'created_at';
	const UPDATED_AT = 'updated_at';

	const STATUS_TRUE = 1;
	const STATUS_FALSE = 0;

	protected $table = 'categories';
	protected $fillable = [
		'father',
		'name',
		'status',
		'friendly_url_id',
		'safe_delete'
	];
	protected $hidden = [
		'getFather',
		'safe_delete'
	];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function products () {
		return $this->belongsToMany('App\Models\Store\Products', 'products_has_categories', 'categories_id', 'products_id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function getFather () {
		return $this->hasOne('App\Models\Store\Categories', 'id', 'father');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function childrens() {
		return $this->hasOne('App\Models\Store\Categories', 'father', 'id');
	}

	/**
	 * @description busca os produtos os quais as categorias os definem exatamente
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function exactProducts () {
		return $this->hasMany('App\Models\Store\Products');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function url () {
		return $this->hasOne('App\Models\Generic\FriendlyUrl', 'id', 'friendly_url_id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function newQuery () {
		$query = parent::newQuery();
		$query->where('safe_delete', '=', 0);
		return $query;
	}

	/**
	 * @param $id
	 * @return bool
	 */
	public static function has ($id) {
		$c = Categories::find($id);
		return !!($c);
	}

	/**
	 * @param $u
	 * @return bool
	 */
	public static function hasUrl ($u) {
		$table = (new FriendlyUrl())->getTable();
		$c = Categories::whereHas('url', function ($query) use ($u, $table) {
			$query->where($table . '.url', '=', $u);
		})
			->count();

		return ($c > 0);
	}

	/**
	 * @param $category
	 */
	protected static function removeHierarchy ($category) {
		$cat = Categories::where('father', '=', $category);

		if ($cat->count() > 0) {
			$categories = $cat->get();

			foreach ($categories as $c) {
				$c->safe_delete = 1;
				$c->save();
				self::removeHierarchy($c->id);
			}
		}
	}

	/**
	 * @param $category
	 * @param $p
	 * @return array
	 */
	public static function listHierarchy ($category, $p, $count) {
		$data = [];

		$where = [];

		$where[] = ['father', '=', $category];
		if ($p['status']) $where[] = ['status', '=', $p['status']];

		$cats = Categories::where($where);

		if ($p['limit'] > 0) {
			$cats->take($p['limit']);

			if ($p['page'] >= 0) {
				$skip = $p['limit'] * $p['page'];
				$cats->skip($skip);
			}
		}


		if ($cats->count() > 0) {
			if ($p['order_column'] && $p['order_by']) {
				$cats->orderBy($p['order_column'], $p['order_by']);
			}

			$categories = $cats->get();
			foreach ($categories as $c) {
				$count++;
				$c->url;
				$c->count = $count;
				$c->status_text = self::getStatusText($c->status);
				$c->childrens = self::listHierarchy($c->id, $p, $count);
				array_push($data, $c);
			}
		}

		return $data;
	}

	/**
	 * @param $r
	 * @param \Closure $success
	 * @param \Closure $error
	 * @return mixed
	 */
	public static function add ($r, \Closure $success, \Closure $error) {
		try {
			$u = FriendlyUrl::create(['url' => $r->url]);
			$c = Categories::create([
				'father' => isset($r->father) ? $r->father : 0,
				'name' => $r->name,
				'status' => $r->status,
				'friendly_url_id' => $u->id
			]);
			return $success($c);
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
	public static function edit ($r, \Closure $success, \Closure $error) {
		try {
			$c = Categories::find($r->id);
			$c->name = $r->name;
			$c->status = $r->status;
			if(!isset($c->url) || empty($c->url)) {
				$url = FriendlyUrl::create([
					'url' => $c->url
				]);
				$c->friendly_url_id = $url->id;
			} else {
				$u = $c->url;
				$u->url = $r->url;
				$u->save();
			}
			$c->save();

			return $success($c);
		} catch (\Exception $e) {
			return $error($e);
		}
	}

	/**
	 * @param $categories
	 * @param \Closure $success
	 * @param \Closure $error
	 * @return mixed
	 */
	public static function remove ($categories, \Closure $success, \Closure $error) {
		try {
			$data = [];

			foreach ($categories as $id) {
				$category = Categories::find($id);

				array_push($data, $category);

				$category->safe_delete = 1;
				$category->save();

				self::removeHierarchy($id);
			}

			return $success($data);
		} catch (\Exception $e) {
			return $error($e);
		}
	}

	/**
	 * @param $c
	 * @return int
	 */
	public static function countHierarchy ($c) {
		$count = 0;
		if (isset($c) && count($c) > 0) {
			$count = count($c);

			foreach ($c as $item) {
				if (count($item->childrens) > 0) {
					$count = self::countHierarchy($item->childrens) + $count;
				}
			}
		}
		return $count;
	}

	/**
	 * @param $r
	 * @param \Closure $success
	 * @param \Closure $error
	 * @return mixed
	 */
	public static function view($r, \Closure $success, \Closure $error) {
		try {

			$cat = Categories::find($r->category);

			$data = [
				'id'=>$cat->id,
				'name' => $cat->name,
				'status' => (int)$cat->status
			];
			if($cat->url) {
				$data['url'] = $cat->url->url;
			} else {
				$data['url'] = '';
			}

			return $success($data);
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
	public static function lists ($r, \Closure $success, \Closure $error) {
		try {
			$data = [];
			$where = [];

			if ($r->hierarchy)
				$where[] = ['father', '=', 0];

			$order_column = $r->order_column ? $r->order_column : 'id';
			$order_by = $r->order_by ? $r->order_by : 'DESC';
			$limit = $r->limit ? $r->limit : null;
			$page = $r->page ? $r->page : null;

			if ($r->status) $where[] = ['status', '=', $r->status];
			if ($r->father != "") $where[] = ['father', '=', $r->father];

			$Cats = Categories::where($where);

			if ($limit > 0) {
				$Cats->take($limit);
				if ($page >= 0) {
					$skip = $limit * $page;
					$Cats->skip($skip);
				}
			}

			$Cats->orderBy($order_column, $order_by);

			$categories = $Cats->get();

			$params = [
				'order_column' => $order_column,
				'order_by' => $order_by,
				'limit' => $limit,
				'page' => $page,
				'status' => $r->status
			];
			$count = 0;
			foreach ($categories as $c) {
				$d = $c;
				$d->url;
				$d->count = $count;
				$d->status = (int)$d->status;
				$d->status_text = self::getStatusText($d->status);
				$d->total_products = $d->products()->count();
				$d->total_childrens = $d->childrens()->count();

				if($c->father > 0) {
					$c->father = $c->getFather;
				} else {
					$c->father = "Não tem";
				}
				if ($r->hierarchy) {
					$d->childrens = self::listHierarchy($c->id, $params, $count);
					$d->count = $count;
					if (count($d->childrens) > 0) {
						$count = $count + self::countHierarchy($d->childrens);
					}

				}
				array_push($data, $d);
				$count++;
			}
			return $success($data);
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
	public static function listProducts ($r, \Closure $success, \Closure $error) {
		try {
			if ($r->category_id)
				$category = Categories::find($r->category_id);
			else {
				$Category = FriendlyUrl::where('url', '=', $r->category_url)->first()->category();

				$category = $Category->first();
			}

			$Products = $category->products();

			$data = Products::getListProducts($Products, $r);

			return $success($data);
		} catch (\Exception $e) {
			return $error($e);
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
}