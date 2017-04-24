<?php
/**
 * User: Marcelo Rafael <marcelo.rafael.feil@gmail.com>
 * Date: 16/04/2017
 */

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Generic\FriendlyUrl;
use App\Models\Store\Brands;
use App\Models\Store\Categories;
use Illuminate\Http\Request;

class BrandsController extends Controller {
	/**
	 * @param $r
	 * @return array
	 */
	protected static function validationNewBrand ($r) {
		$m = [];

		if (!isset($r->name) || empty($r->name)) {
			$m['name'] = 'O campo nome é obrigatório.';
		} else {
			if (strlen($r->name) <= 5) {
				$m['name'] = 'O valor atribuído para o campo nome, é inválido.';
			}
		}
		if (!isset($r->status)) {
			$m['status'] = 'O campo status é obrigatório.';
		} else {
			if (
				!is_numeric($r->status) ||
				(
					$r->status != Brands::STATUS_TRUE &&
					$r->status != Brands::STATUS_FALSE
				)
			) {
				$m['url'] = 'O valor atribuíro para o campo status, é inválido.';
			}
		}
		if (!isset($r->url) || empty($r->url)) {
			$m['url'] = 'O campo url é obrigatório';
		} else {
			if (FriendlyUrl::has($r->url)) {
				$m['url'] = 'O valor atribuído para o campo url, já está em uso.';
			}
		}

		return $m;
	}

	/**
	 * @param $r
	 * @return array
	 */
	protected static function validationUpdateBrand ($r) {
		$m = [];

		if (isset($r->id)) {
			if (!Brands::has($r->id)) {
				$m['brand'] = 'A marca que está tentando editar, não existe.';
			}
		}

		if (count($m) == 0) {
			if (!isset($r->name) || empty($r->name)) {
				$m['name'] = 'O campo nome é obrigatório.';
			} else {
				if (strlen($r->name) <= 5) {
					$m['name'] = 'O valor atribuído para o campo nome, é inválido.';
				}
			}
			if (!isset($r->status)) {
				$m['status'] = 'O campo status é obrigatório.';
			} else {
				if (
					!is_numeric($r->status) ||
					(
						$r->status != Brands::STATUS_TRUE &&
						$r->status != Brands::STATUS_FALSE
					)
				) {
					$m['url'] = 'O valor atribuíro para o campo status, é inválido.';
				}
			}
			if (!isset($r->url) || empty($r->url)) {
				$m['url'] = 'O campo url é obrigatório';
			} else {
				if (FriendlyUrl::has($r->url, 'brands', $r->id)) {
					$m['url'] = 'O valor atribuído para o campo url, já está em uso.';
				}
			}
		}

		return $m;
	}

	/**
	 * @param $r
	 * @return array
	 */
	protected static function validationListBrands ($r) {
		$m = [];

		$columns = ['id', 'name', 'created_at', 'updated_at', 'status'];
		$orders = ['ASC', 'asc', 'DESC', 'desc'];
		if (!empty($r->status)) {
			if (
				!is_numeric($r->status) ||
				(
					$r->status != Brands::STATUS_TRUE &&
					$r->status != Brands::STATUS_FALSE
				)
			) {
				$m['status'] = 'O valor atribuído para o campo status, é inválido.';
			}
		}
		if (!empty($r->order_column)) {
			if (!in_array($r->order_column, $columns)) {
				$m['columns'] = 'O valor atribuído para o campo columns, é inválido.';
			}
		}
		if (!empty($r->order_by)) {
			if (!in_array($r->order_by, $orders)) {
				$m['orders'] = 'O valor atribuído para o campo orders, é inválido.';
			}
		}
		if (!empty($r->page)) {
			if (!is_numeric($r->page) && $r->page < 0) {
				$m['page'] = 'O valor atribuído para o campo page, é inválido.';
			}
		}
		if (!empty($r->limit)) {
			if (!is_numeric($r->limit) && $r->limit < 0) {
				$m['limit'] = 'O valor atribuído para o campo limit, é inválido.';
			}
		}

		return $m;
	}

	/**
	 * @param $r
	 * @return array
	 */
	protected static function validationRemoveBrands ($r) {
		$m = [];

		if (count($r->brands) > 0) {
			foreach ($r->brands as $b) {
				if (!Brands::has($b)) {
					$m['brands'] = 'A marca "' . $b . '", não foi encontrada.';
				}
			}
		} else {
			$m['brands'] = 'É necessário selecionar a marca que deseja apagar.';
		}

		return $m;
	}

	private static function validationViewBrand ($r) {
		$m = [];

		if(!isset($r->brand) || empty($r->brand)) {
			$m['brand'] = 'É necessário selecionar a marca.';
		} else {
			if(!Brands::has($r->brand)) {
				$m['brand'] = 'A marca selecionada não foi encontrada';
			}
		}

		return $m;
	}

	/**
	 * @param $r
	 * @return mixed
	 */
	protected static function validationListProductsOfBrand ($r) {
		$pb = new ProductsController();
		return $pb->validation($r, function () use ($r) {
			$m = [];
			if (!empty($r->brand_id) || !empty($r->brand_url)) {
				if (!empty($r->brand_id)) {
					if (!Brands::has($r->brand_id)) {
						$m['brand'] = 'A marca selecionada não foi encontrada.';
					}
				} else if (!empty($r->brand_url)) {
					if (!Brands::hasUrl($r->brand_url)) {
						$m['brand'] = 'A marca selecionada não foi encontrada.';
					}
				}
			} else {
				$m['brand'] = 'É necessário informar a marca da qual deseja listar os produtos.';
			}

			return $m;
		}, function ($m) {
			return $m;
		});
	}

	/**
	 * @param $r
	 * @param \Closure $success
	 * @param \Closure $error
	 * @return mixed
	 */
	private function validation ($r, \Closure $success, \Closure $error) {
		$m = [];
		$e = new \Exception();
		$call = $e->getTrace()[1]['function'];

		switch ($call) {
			case 'newBrand' :
				$m = self::validationNewBrand($r);
				break;
			case 'updateBrand' :
				$m = self::validationUpdateBrand($r);
				break;
			case 'removeBrands' :
				$m = self::validationRemoveBrands($r);
				break;
			case 'listBrands' :
				$m = self::validationListBrands($r);
				break;
			case 'viewBrand':
				$m = self::validationViewBrand($r);
				break;
			case 'listProductsOfBrand':
				$m = self::validationListProductsOfBrand($r);
				break;
		}

		if (count($m) == 0) {
			return $success();
		} else {
			return $error($m);
		}
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function newBrand (Request $request) {
		return $this->validation($request, function () use ($request) {
			return Brands::add($request, function ($data) use ($request) {
				return \Response::json([
					'success' => [
						'message' => 'Marca cadastrada com sucesso.',
						'data' => $data
					]
				], 200);
			}, function ($e) {
				return \Response::json([
					'error' => [
						'internal' => [
							'message' => $e->getMessage(),
							'file' => $e->getFile(),
							'line' => $e->getLine()
						]
					]
				], 500);
			});
		}, function ($m) {
			return \Response::json([
				'errors' => [
					'messages' => $m,
				]
			],400);
		});
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function updateBrand (Request $request) {
		return $this->validation($request, function ($m) use ($request) {
			return Brands::edit($request, function ($data) {
				return \Response::json([
					'success' => [
						'message' => 'Marca atualizada com sucesso',
						'data' => $data
					]
				], 200);
			}, function ($e) {
				return \Response::json([
					'error' => [
						'internal' => [
							'message' => $e->getMessage(),
							'file' => $e->getFile(),
							'line' => $e->getLine()
						],
						'message' => 'Erro interno. Tente novamente mais tarde.'
					]
				], 500);
			});
		}, function ($m) {
			return \Response::json([
				'errors' => [
					'messages' => $m
				]
			], 400);
		});
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function removeBrands (Request $request) {
		return $this->validation($request, function () use ($request) {
			return Brands::remove($request->brands, function ($data) {
				return \Response::json([
					'success' => [
						'message' => 'Marcas apagadas com sucesso.',
						'data' => $data
					]
				], 500);
			}, function ($e) {
				return \Response::json([
					'error' => [
						'internal' => [
							'message' => $e->getMessage(),
							'file' => $e->getFile(),
							'line' => $e->getLine()
						]
					]
				], 500);
			});
		}, function ($m) {
			return \Response::json([
				'errors' => [
					'message' => $m
				]
			], 404);
		});
	}

	/***
	 * @param Request $r
	 * @return mixed
	 */
	public function listBrands (Request $r) {
		return $this->validation($r, function () use ($r) {
			return Brands::lists($r, function ($data) {
				return \Response::json([
					'success' => [
						'message' => 'Marcas retornados com sucesso.',
						'data' => $data
					]
				], 200);
			}, function ($e) {
				return \Response::json([
					'error' => [
						'internal' => [
							'message' => $e->getMessage(),
							'file' => $e->getFile(),
							'line' => $e->getLine()
						]
					]
				], 500);
			});
		}, function ($m) {
			return \Response::json([
				'errors' => [
					'messages' => $m
				]
			], 400);
		});
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function listProductsOfBrand (Request $request) {
		return $this->validation($request, function () use ($request) {
			return Brands::listProducts($request, function ($data) {
				return \Response::json([
					'success' => [
						'message' => 'Produtos retornados com sucesso.',
						'data' => $data
					]
				]);
			}, function ($e) {
				return \Response::json([
					'error' => [
						'internal' => [
							'message' => $e->getMessage(),
							'file' => $e->getFile(),
							'line' => $e->getLine()
						],
						'message' => 'Erro interno. Tente novamente mais tarde.'
					]
				], 500);
			});
		}, function ($m) {
			return \Response::json([
				'errors' => [
					'messages' => $m
				]
			], 400);
		});
	}

	public function viewBrand (Request $r) {
		return $this->validation($r, function() use ($r) {
			return Brands::view($r, function($data) {
				return \Response::json([
					'success' => [
						'message' => 'Marca retornada com sucesso.',
						'data' => $data
					]
				], 200);
			}, function($e) {
				return \Response::json([
					'error' => [
						'message' => 'Erro interno. Tente novamente mais tarde',
						'errors' => [
							'message' => $e->getMessage(),
							'file' => $e->getFile(),
							'line' => $e->getLine()
						]
					]
				], 500);
			});
		}, function($m) {
			return \Response::json([
				'errors' => [
					'messages' => $m
				]
			], 400);
		});
	}
}