<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Generic\FriendlyUrl;
use App\Models\Store\Brands;
use App\Models\Store\Categories;
use App\Models\Store\Products;
use Illuminate\Http\Request;

class ProductsController extends Controller {
	/**
	 * @param $d
	 * @param \Closure $success
	 * @param \Closure $error
	 * @return mixed
	 */
	public function validation ($d, \Closure $success, \Closure $error) {
		$m = [];
		$e = new \Exception();
		$call = $e->getTrace()[1]['function'];
		// Se o métood que chamou, foi o metodo que lista os produtos, a validação será diferente.
		if($call == 'viewProduct') {
			if(empty($d)) {
				$m['product'] = ['message' => 'É necessário selecionar o produto que deseja visualizar.'];
			} else {
				if(!FriendlyUrl::has($d, 'products')) {
					$m['product'] = ['message' => 'Produto não encontrado.'];
				}
			}
		} else {
			if (
				$call == 'listProducts' ||
				$call == 'validationListProductsOfCategories' ||
				$call == 'validationListProductsOfBrand'
			) {
				// validação de ordenação
				$columns = ['id', 'name', 'prices', 'created_at', 'updated_at', 'status', 'featured'];
				$orders = ['ASC', 'asc', 'DESC', 'desc'];
				if (!empty($d->featured)) {
					if (
						!is_numeric($d->featured) ||
						(
							$d->featured != Products::FEATURED_TRUE &&
							$d->featured != Products::FEATURED_FALSE
						)
					) {
						$m['featured'] = 'O valor atribuído para o campo destaque, é inválido.';
					}
				}
				if (!empty($d->status)) {
					if (
						!is_numeric($d->status) ||
						(
							$d->status != Products::STATUS_TRUE &&
							$d->status != Products::STATUS_FALSE
						)
					) {
						$m['status'] = 'O valor atribuído para o campo status, é inválido.';
					}
				}
				if (!empty($d->category_id)) {
					if (!is_numeric($d->category_id) || $d->category_id <= 0) {
						$m['category'] = 'O valor atribuído para o campo categoria, é inválido.';
					} else if (!Categories::has($d->category_id)) {
						$m['category'] = 'A categoria informada não foi encontrada.';
					}
				}
				if (!empty($d->category_url)) {
					if (!Categories::hasUrl($d->category_url)) {
						$m['category'] = 'A categoria informada não foi encontrada.';
					}
				}
				if (!empty($d->order_column)) {
					if (!in_array($d->order_column, $columns)) {
						$m['order_column'] = 'O valor informado para o campo "coluna de ordenação", é inválido.';
					}
				}
				if (!empty($d->order_by)) {
					if (!in_array($d->order_by, $orders)) {
						$m['order_by'] = 'O valor informado para o campo "ordem", é inválido.';
					}
				}
				if (!empty($d->page)) {
					if (!is_numeric($d->page) && $d->page < 0) {
						$m['page'] = 'O valor atribuído para o campo "página", é inválido.';
					}
				}
				if (!empty($d->limit)) {
					if (!is_numeric($d->limit) && $d->limit < 0) {
						$m['limit'] = 'O valor atribuído para o campo "limite", é inválido.';
					}
				}
			} else if ($call == 'removeProducts') {
				if (count($d->products) > 0) {
					foreach ($d->products as $p) {
						if (!Products::has($p)) {
							$m['products'] = ['message' => 'O produto "' . $p . '", não foi encontrado.'];
						}
					}
				} else {
					$m['products'] = ['É necessário selecionar o produto que deseja apagar.'];
				}
			} else {
				if ($call == 'updateProduct') {
					$Products = Products::find($d->id);
					if (!$Products) {
						$m['product'] = ['message' => 'O produto selecionado não existe.'];
					}
				}
				if (count($m) == 0) {
					if (empty($d->name)) {
						$m['name'] = 'O campo nome é obrigatório.';
					} else {
						if (strlen($d->name) <= 5) {
							$m['name'] = 'O nome informado é inválido.';
						}
					}
					if (empty($d->url)) {
						$m['url'] = 'O campo url é obrigatório.';
					} else {
						if ($e->getTrace()[1]['function'] == 'updateProduct') {
							if (FriendlyUrl::has($d->url, 'products', $d->id)) {
								$m['url'] = 'O valor atribuído no campo URL, já está em uso.';
							}
						} else {
							if (FriendlyUrl::has($d->url)) {
								$m['url'] = 'O valor atribuído no campo URL, já está em uso.';
							}
						}
					}
					if (!empty($d->code)) {
						if (Products::hasCode($d->code, $d->id)) {
							$m['code'] = 'O valor atribuído ao campo código, já está em uso.';
						}
					}
					if (!empty($d->quantity)) {
						if (!is_numeric($d->quantity) || $d->quantity < 0) {
							$m['quantity'] = 'A quantidade informado é inválida.';
						}
					}
					if (!empty($d->featured)) {
						if (
							!is_numeric($d->featured) ||
							(
								$d->featured != Products::FEATURED_TRUE &&
								$d->featured != Products::FEATURED_FALSE
							)
						) {
							$m['featured'] = 'O valor atribuido para o campo destaque, é inválido.';
						}
					}
					if (!empty($d->status)) {
						if (
							!is_numeric($d->status) ||
							(
								$d->status != Products::STATUS_TRUE &&
								$d->status != Products::STATUS_FALSE
							)
						) {
							$m['status'] = 'O valor atribuído no campo status, é inválido.';
						}
					}
					if (!empty($d->width) && !is_numeric($d->width)) {
						$m['width'] = 'O valor atribuído para o campo largura, é inválido.';
					}
					if (!empty($d->height) && !is_numeric($d->height)) {
						$m['height'] = 'O valor atribuído para o campo altura, é inválido.';
					}
					if (!empty($d->length) && !is_numeric($d->length)) {
						$m['length'] = 'O valor atribuído para o campo comprimento, é inválido.';
					}
					if (!empty($d->diameter) && !is_numeric($d->diameter)) {
						$m['diameter'] = 'O valor atribuído para o campo diâmetro, é inválido.';
					}
					if (!empty($d->weight) && !is_numeric($d->weight)) {
						$m['weight'] = 'O valor atribuído para o campo peso, é inválido.';
					}
					if (!empty($d->category)) {
						if (!is_numeric($d->category)) {
							$m['category'] = 'O valor atribuído para o campo categoria, é inválido.';
						} else if (!Categories::has($d->category)) {
							$m['category'] = 'O valor atribuído para o campo categoria, não existe.';
						}
					}
					if (!empty($d->brand)) {
						if (!is_numeric($d->brand)) {
							$m['brand'] = 'O valor atribuído para o campo marca, é inválido.';
						} else if (!Brands::has($d->brand)) {
							$m['brand'] = 'O valor atribuído para o campo marca, não existe.';
						}
					}
				}
			}
		}
		if(count($m) == 0) {
			return $success();
		} else {
			return $error($m);
		}
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function newProduct (Request $request) {
		return $this->validation($request, function () use ($request) {
			return Products::add($request, function ($r) {
					return \Response::json([
						'success' => [
							'message' => 'Produto cadastrado com sucesso.',
							'data' => $r
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
		}, function($m) {
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
	public function updateProduct (Request $request) {
		return $this->validation($request, function () use ($request) {
			return Products::edit($request, function ($r) {
				return \Response::json([
					'success' => [
						'message' => 'Produto atualizado com sucesso.',
						'data' => $r
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
		}, function($m) {
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
	public function removeProducts (Request $request) {
		return $this->validation($request, function() use ($request) {
			return Products::remove($request->products, function ($data) {
				return \Response::json([
					'success' => [
						'message' => 'Produtos apagados com sucesso.',
						'data' => $data
					]
				], 200);
			}, function ($e) {
				return \Response::json([
					'error' => [
						'message' => 'Erro interno. Tente novamente mais tarde.',
						'internal' => [
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
					'message' => $m
				]
			], 400);
		});
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function listProducts (Request $request) {
		return $this->validation($request, function () use ($request) {
			return Products::lists($request, function ($data) {
					if (count($data) > 0) {
						return \Response::json([
							'success' => [
								'message' => 'Produtos retornados com sucesso',
								'data' => $data
							]
						]);
					} else {
						return \Response::json([
							'error' => [
								'message' => 'Produtos não encontrado.'
							]
						], 404);
					}
				}, function ($e) {
					return \Response::json([
						'error' => [
							'message' => 'Erro interno. Tente novamente mais tarde.',
							'internal' => [
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

	/**
	 * @param $url
	 * @return mixed
	 */
	public function viewProduct ($url) {
		return $this->validation($url, function() use ($url) {
			return Products::view($url, function($data) {
				return \Response::json([
					'success' => [
						'message' => 'Produto retornado com sucesso.',
						'data' => $data
					]
				],200);
			}, function($e) {
				return \Response::json([
					'error' => [
						'internal' => [
							'message' => $e->getMessage(),
							'line' => $e->getLine(),
							'file' => $e->getFile()
						],
						'message' => 'Erro interno. Tente novamente mais tarde.'
					]
				]);
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
