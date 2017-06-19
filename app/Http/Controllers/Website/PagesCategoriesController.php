<?php

namespace App\Http\Controllers\Website;

use App\Models\Generic\FriendlyUrl;
use App\Models\Website\PagesCategories;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * Class PagesCategoriesController
 * @package App\Http\Controllers\Website
 */
class PagesCategoriesController extends Controller {
	/**
	 * @param $r
	 * @return array
	 */
	private function validateNewPagesCategory ($r) {
		$m = [];
		if (empty($r->name)) {
			$m['name'] = 'O campo nome é obrigatório.';
		}
		if (!empty($r->father)) {
			if (!PagesCategories::has($r->father)) {
				$m['father'] = 'A categoria pai é inválida.';
			}
		}
		if (!isset($r->status) || $r->status === '') {
			$m['status'] = 'O campo status é obrigatório.';
		} else {
			if (
				!is_numeric($r->status) ||
				(
					$r->status != PagesCategories::STATUS_FALSE &&
					$r->status != PagesCategories::STATUS_TRUE
				)
			) {
				$m['status'] = 'O valor atribuído para o campo status, é inválido.';
			}
		}
		if (empty($r->url)) {
			$m['url'] = 'O campo url é obrigatório.';
		} else {
			if (FriendlyUrl::has($r->url)) {
				$m['url'] = 'O valor atribuído para o campo URL, já está em uso.';
			}
		}

		return $m;
	}

	/**
	 * @param $r
	 * @return array
	 */
	private function validateViewPagesCategory ($r) {
		$m = [];

		if(empty($r->category)) {
			$m['category'] = 'É necessário selecionar a categoria.';
		} else {
			if(!PagesCategories::has($r->category)) {
				$m['category'] = 'A categoria selecionada não foi encontrada.';
			}
		}

		return $m;
	}

	/**
	 * @param $r
	 * @return array
	 */
	private function validateUpdatePagesCategory($r) {
		$m = [];
		if(empty($r->id)) {
			$m['category'] = 'É necessário selecionar a categoria que deseja editar.';
		} else {
			if(!PagesCategories::has($r->id)) {
				$m['category'] = 'Categoria não encontrada.';
			}
		}
		if(count($m) == 0) {
			if (empty($r->name)) {
				$m['name'] = 'O campo name é obrigatório.';
			}
			if (!empty($r->father)) {
				if (!PagesCategories::has($r->father)) {
					$m['father'] = 'A categoria pai é inválida.';
				}
			}
			if (!isset($r->status) || $r->status === '') {
				$m['status'] = 'O campo status é obrigatório.';
			} else {
				if (
					!is_numeric($r->status) ||
					(
						$r->status != PagesCategories::STATUS_FALSE &&
						$r->status != PagesCategories::STATUS_TRUE
					)
				) {
					$m['status'] = 'O valor atribuído para o campo status, é inválido.';
				}
			}
			if (empty($r->url)) {
				$m['url'] = 'O campo URL é obrigatório.';
			} else {
				if (FriendlyUrl::has($r->url, 'pages_categories', $r->id)) {
					$m['url'] = 'O valor atribuído para o campo URL, já está em uso.';
				}
			}
		}
		return $m;
	}

	/**
	 * @param $r
	 * @return array
	 */
	private function validateRemovePagesCategories($r) {
		$m = [];
		if(count($r->categories) == 0) {
			$m['category'] = 'É necessário selecionar as categorias que deseja remover.';
		} else {
			foreach($r->categories as $c) {
				if(!PagesCategories::has($c)) {
					$m['category'] = 'A categoria "' . $c . '", não foi encontrada.';
				}
			}
		}
		return $m;
	}

	/**
	 * @param $r
	 * @return array
	 */
	private function validateListPagesCategories($r) {
		$m = [];
		$columns = ['id', 'name', 'status', 'created_at', 'updated_at'];
		$orders = ['asc', 'ASC', 'desc', 'DESC'];

		if(!empty($r->order_by)) {
			if(!in_array($r->order_by, $orders)) {
				$m['order_by'] = 'O valor atribuído para o campo ordem, é inválido.';
			} else if (empty($r->order_by)) {
				$m['order_by'] = 'O campo order é obrigatório enquanto o campo coluna estiver preenchido.';
			}
		}
		if(!empty($r->order_column)) {
			if(!in_array($r->order_column, $columns)) {
				$m['order_column'] = 'O valor atribuído para o campo coluna, é inválido';
			} else if (empty($r->order_column)) {
				$m['order_column'] = 'O campo coluna é obrigatório enquanto o campo ordem estiver preenchido.';
			}
		}
		if(!empty($r->limit)) {
			if(!is_numeric($r->limit) || $r->limit < 0) {
				$m['limit'] = 'O valor atribúdo para o campo limite, é inválido.';
			}
		}
		if($r->page) {
			if(!is_numeric($r->page) || $r->page < 0) {
				$m['page'] = 'O valor atribuído para o cmapo page, é inváliod.';
			} else if(empty($r->limit)) {
				$m['limit'] = 'O campo limite é obrigatório enquanto o campo page estiver preenchido.';
			}
		}
		if(isset($r->status) && $r->status != '') {
			if(
				!is_numeric($r->status) ||
				(
					$r->status != PagesCategories::STATUS_FALSE ||
					$r->status != PagesCategories::STATUS_TRUE
				)
			) {
				$m['status'] = 'O valor atribuído para o campo status, é inválido.';
			}
		}

		return $m;
	}

	/**
	 * @param $r
	 * @return mixed
	 */
	private function validationListPagesOfPagesCategories($r) {
		$pc = new PagesController();
		return $pc->validation($r, function() use ($r) {
			$m=[];
			if(empty($r->category_id) && empty($r->category_url)) {
				$m['category'] = 'É necessário informar o código ou url da categoria.';
			}
			return $m;
		}, function($m) {
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

		switch($call) {
			case 'newCategory' :
				$m = self::validateNewPagesCategory($r);
				break;
			case 'updateCategory' :
				$m = self::validateUpdatePagesCategory($r);
				break;
			case 'removeCategories' :
				$m = self::validateRemovePagesCategories($r);
				break;
			case 'listCategories' :
				$m = self::validateListPagesCategories($r);
				break;
			case 'viewCategory' :
				$m = self::validateViewPagesCategory($r);
				break;
			case 'listPagesOfPagesCategories' :
				$m = self::validationListPagesOfPagesCategories($r);
				break;
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
	public function newCategory (Request $request) {
		return $this->validation($request, function () use ($request) {
			return PagesCategories::add($request, function($data) {
				return \Response::json([
					'success' => [
						'message' => 'Categoria adicionada com sucesso.',
						'data' => $data
					]
				], 200);
			}, function($e) {
				return \Response::json([
					'error' => [
						'internal' => $e->getMessage(),
						'file' => $e->getFile(),
						'line' => $e->getLine()
					],
					'message' => 'Erro interno. Tente novamente mais tarde.'
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
	public function updateCategory (Request $request) {
		return $this->validation($request, function() use ($request) {
			return PagesCategories::edit($request, function($data) {
				return \Response::json([
					'success' => [
						'message' => 'Categoria editada com sucesso.',
						'data' => $data
					]
				], 200);
			}, function($e) {
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
	public function removeCategories (Request $request) {
		return $this->validation($request, function() use ($request) {
			return PagesCategories::remove($request->categories, function($data) {
				return \Response::json([
					'success' => [
						'message' => 'Categorias apagadas com sucesso.',
						'data' => $data
					]
				],200);
			}, function($e) {
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
	public function listCategories(Request $request) {
		return $this->validation($request, function() use ($request) {
			return PagesCategories::lists($request, function($data) {
				return \Response::json([
					'success' => [
						'message' => 'Categorias retornadas com sucesso.',
						'data' => $data
					]
				]);
			}, function($e) {
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
			]);
		});
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function listPagesOfPagesCategories(Request $request) {
		return $this->validation($request, function() use ($request) {
			return PagesCategories::listPages($request, function($data) {
				return \Response::json([
					'success' => [
						'message' => 'Dados retornados com sucesso.',
						'data' => $data
					]
				],200);
			}, function($e) {
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
	 * @param Request $r
	 * @return mixed
	 */
	public function viewCategory($id, Request $r) {
		$r->category = $id;
		return $this->validation($r, function() use ($r) {
			return PagesCategories::view($r, function($data) {
				return \Response::json([
					'success' => [
						'message' => 'Categoria retornada com sucesso.',
						'data' => $data
					]
				], 200);
			}, function($e) {
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
			],400);
		});
	}
}
