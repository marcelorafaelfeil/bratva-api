<?php

namespace App\Http\Controllers\Website;

use App\Libraries\Utils;
use App\Models\Generic\FriendlyUrl;
use App\Models\Website\Pages;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PagesController extends Controller {
	/**
	 * @param $r
	 * @return array
	 */
	protected static function validateDates ($r) {
		$m = [];
		if ($r->expire == Pages::EXPIRE_TRUE) {
			if (empty($r->date_start)) {
				$m['date_start'] = 'O campo da data de início de exibição, é obrigatório.';
			}
			if (empty($r->date_end)) {
				$m['date_end'] = 'O campo da data de fim de exibição, é obrigatório.';
			}
			if (!empty($r->date_start) && !empty($r->date_end)) {
				$valid = false;
				if (!Utils::ValidateDate($r->date_start)) {
					$m['date_start'] = 'O valor atribuído para o campo data de inicío de exibição, é inválido.';
				} else $valid = true;
				if (!Utils::ValidateDate($r->date_end)) {
					$m['date_end'] = 'O valora tribuído para o campo data de fim de exibição, é inválido.';
					$valid = false;
				}

				if ($valid) {
					$dateStart = new \DateTime($r->date_start);
					$dateEnd = new \DateTime($r->date_end);

					if ($dateStart >= $dateEnd) {
						$m['date_end'] = 'A data de fim de exibição deve ser maior que a data de início de exibição.';
					}
				}
			}
		}
		return $m;
	}

	/**
	 * @param $r
	 * @return array
	 */
	protected static function simpleValidation ($r) {
		$m = [];

		if (!empty($r->expire)) {
			if (
				!is_numeric($r->expire) ||
				(
					$r->expire != Pages::EXPIRE_FALSE &&
					$r->expire != Pages::EXPIRE_TRUE
				)
			) {
				$m['expire'] = 'O valor atribuído para o campo expirar, é inválido.';
			}
		}
		if (!isset($r->status) || $r->status === '') {
			$m['status'] = 'O campo status é obrigatório.';
		} else {
			if (
				!is_numeric($r->status) ||
				(
					$r->status != Pages::STATUS_FALSE &&
					$r->status != Pages::STATUS_TRUE
				)
			) {
				$m['status'] = 'O valor atribuído para o campo status, é inválido.';
			}
		}

		return $m;
	}

	/**
	 * @param $r
	 * @param null $id
	 * @return array
	 */
	protected static function validateFriendlyUrl($r,$id=null) {
		$m = [];

		if(empty($r->url)) {
			$m['url'] = 'O campo URL é obrigatório.';
		} else {
			if($id) {
				if (FriendlyUrl::has($r->url, 'pages', $r->id)) {
					$m['url'] = 'A URL informada já está em uso.';
				}
			} else {
				if (FriendlyUrl::has($r->url)) {
					$m['url'] = 'A URL informada já está em uso.';
				}
			}
		}

		return $m;
	}

	/**
	 * @param $r
	 * @return array
	 */
	protected static function validationNewPage($r) {
		$m = [];
		if(empty($r->title)) {
			$m['title'] = 'O campo titulo é obrigatório.';
		}
		if($messages = self::validateFriendlyUrl($r)) {
			foreach ($messages as $k => $v) {
				$m[$k] = $v;
			}
		}
		if($messages = self::simpleValidation($r)) {
			foreach ($messages as $k => $v) {
				$m[$k] = $v;
			}
		}
		if($messages = self::validateDates($r)) {
			foreach ($messages as $k => $v) {
				$m[$k] = $v;
			}
		}
		return $m;
	}

	/**
	 * @param $r
	 * @return array
	 */
	public static function validationUpdatePage($r) {
		$m = [];
		if(empty($r->id)) {
			$m['page'] = 'É necessário selecionar a página que deseja apagar.';
		} else {
			if (empty($r->title)) {
				$m['title'] = 'O campo título é obrigatório.';
			}
			if ($messages = self::validateFriendlyUrl($r, $r->id)) {
				foreach ($messages as $k => $v) {
					$m[$k] = $v;
				}
			}
			if ($messages = self::simpleValidation($r)) {
				foreach ($messages as $k => $v) {
					$m[$k] = $v;
				}
			}
			if ($messages = self::validateDates($r)) {
				foreach ($messages as $k => $v) {
					$m[$k] = $v;
				}
			}
		}
		return $m;
	}

	/**
	 * @param $r
	 * @return array
	 */
	public static function validationRemovePages($r) {
		$m = [];
		if(count($r->pages) == 0) {
			$m['pages'] = 'É necessário selecionar a página que deseja apagar.';
		} else {
			foreach($r->pages as $p) {
				if(!Pages::has($p)) {
					$m['pages'] = 'A página "' . $p . '", não foi encontrada.';
					break;
				}
			}
		}
		return $m;
	}

	/**
	 * @param $r
	 * @return array
	 */
	public static function validationListPages($r) {
		$m = [];
		$orders = ['asc', 'ASC', 'desc', 'DESC'];
		$columns = ['id', 'title', 'created_at', 'updated_at'];

		if(!empty($r->orderBy) && !in_array($r->orderBy, $orders)) {
			$m['orderBy'] = 'O valor atribuído para o campo orderBy, é inválido.';
		}
		if(!empty($r->orderColumn && !in_array($r->orderColumn, $columns))) {
			$m['orderColumns'] = 'O valor atribuído para o campo orderColumns, é inválido.';
		}
		if(!empty($r->activeds) && !is_numeric($r->activeds)) {
			$m['activeds'] = 'O valora tribuído para o campo activeds, é inválido.';
		}
		if(!empty($r->limit) && !is_numeric($r->limit)) {
			$m['limit'] = 'O valor atribuído para o campo limit, é inválido.';
		}
		if(!empty($r->page) && !is_numeric($r->page)) {
			$m['page'] = 'O valor atribuído para o campo page, é inválido.';
		}

		return $m;
	}

	/**
	 * @param $url
	 * @return array
	 */
	public static function validationViewByUrl($url) {
		$m = [];
		if(empty($url)) {
			$m['page'] = 'É necessário informar a URL da página.';
		} else {
			if(!Pages::hasByUrl($url)) {
				$m['page'] = 'Página não encontrada.';
			}
		}


		return $m;
	}

	/**
	 * @param $id
	 * @return array
	 */
	private function validationViewById($id) {
		$m = [];

		if(empty($id)) {
			$m['page'] = 'É necessário selecionar a página.';
		} else {
			if(!Pages::has($id)) {
				$m['page'] = 'Página não encontrada.';
			}
		}

		return $m;
	}

	private function validationFeaturedImage($d) {
		$m = [];

		if(!isset($d->page) || empty($d->page)) {
			$m['page'] = ['message' => 'Não foi possível identificar a página.'];
		} else {
			if(!Pages::has($d->page)) {
				$m['page'] = ['message' => 'A página informada não foi encontrada.'];
			}
		}
		if(!isset($d->image) || empty($d->image)) {
			$m['image'] = ['message' => 'É necessário selecionar a imagem que deseja marcar como principal.'];
		} else {
			if(!Pages::hasImage($d->page, $d->image)) {
				$m['image'] = ['message' => 'A imagem selecionada não foi encontrada neste produto.'];
			}
		}

		return $m;
	}

	/**
	 * @param $r
	 * @param \Closure $success
	 * @param \Closure $error
	 * @return mixed
	 */
	public function validation($r, \Closure $success, \Closure $error) {
		$m = [];
		$e = new \Exception();
		$call = $e->getTrace()[1]['function'];

		switch($call) {
			case 'newPage':
				$m = self::validationNewPage($r);
				break;
			case 'updatePage':
				$m = self::validationUpdatePage($r);
				break;
			case 'removePages':
				$m = self::validationRemovePages($r);
				break;
			case 'listPages':
				$m = self::validationListPages($r);
				break;
			case 'viewPageByUrl':
				$m = self::validationViewByUrl($r);
				break;
			case 'viewPageById':
				$m = self::validationViewById($r);
				break;
			case 'featuredImage':
				$m = self::validationFeaturedImage($r);
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
	public function newPage(Request $request) {
		return $this->validation($request, function() use ($request) {
			return Pages::add($request, function ($data) {
				return \Response::json([
					'success' => [
						'message' => 'Página criada com sucesso.',
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
	public function updatePage(Request $request) {
		return $this->validation($request, function() use ($request) {
			return Pages::edit($request, function($data) {
				return \Response::json([
					'success' => [
						'message' => 'Pagina alterada com sucesso.',
						'data' => $data
					]
				]);
			}, function($e) {
				return \Response::json([
					'error'=> [
						'internal' => [
							'message' => $e->getMessage(),
							'file' => $e->getFile(),
							'line' => $e->getLine()
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

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function removePages(Request $request) {
		return $this->validation($request, function() use ($request) {
			return Pages::remove($request, function($data) {
				return \Response::json([
					'success' => [
						'message' => 'Páginas apagadas com sucesso.',
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
	public function listPages(Request $request) {
		return $this->validation($request, function() use ($request) {
			return Pages::lists($request, function($data) {
				return \Response::json([
					'success' => [
						'messages' => 'Páginas retornadas com sucesso',
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

	/**
	 * @param $code
	 * @return mixed
	 */
	public function viewPageById($code) {
		return $this->validation($code, function() use ($code) {
			return Pages::view($code, function($data) {
				return \Response::json([
					'success' => [
						'message' => 'Página retornada com sucesso.',
						'data' => $data
					]
				], 200);
			}, function($e) {
				return \Response::json([
					'error' => [
						'internal' => [
							'message' => $e->getMessage(),
							'file' =>  $e->getFile(),
							'line' => $e->getLine()
						],
						'message' => 'Erro interno. Tente novamente mais tarde.'
					]
				], 400);
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
	 * @param $url
	 * @return mixed
	 */
	public function viewPageByUrl($url) {
		return $this->validation($url, function() use ($url) {
			return Pages::viewByUrl($url, function($data) {
				return \Response::json([
					'success' => [
						'message' => 'Página retornada com sucesso.',
						'data' => $data
					]
				], 200);
			}, function($e) {
				return \Response::json([
					'error' => [
						'internal' => [
							'message' => $e->getMessage(),
							'file' =>  $e->getFile(),
							'line' => $e->getLine()
						],
						'message' => 'Erro interno. Tente novamente mais tarde.'
					]
				], 400);
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
	 * @param Request $r
	 * @return mixed
	 */
	public function featuredImage (Request $r) {
		return $this->validation($r, function() use ($r) {
			return Pages::featuredImage($r, function($data) {
				return \Response::json([
					'success' => [
						'message' => 'Imagem alterada com sucesso.',
						'data' => $data
					]
				],200);
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
					'messages' => $m
				]
			], 400);
		});
	}
}
