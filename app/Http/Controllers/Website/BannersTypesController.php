<?php

namespace App\Http\Controllers\Website;

use App\Libraries\Utils;
use App\Models\Website\BannersTypes;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BannersTypesController extends Controller {

	/**
	 * @param $r
	 * @return array
	 */
	protected static function validationNewType ($r) {
		$m = [];
		if (empty($r->title)) {
			$m['title'] = ['message' => 'O campo título é obrigatório.'];
		}
		if (!isset($r->status) || $r->status == "") {
			$m['status'] = ['message' => 'O campo status é obrigatório.'];
		} else {
			if (
				!is_numeric($r->status) ||
				(
					$r->status != BannersTypes::STATUS_FALSE &&
					$r->status != BannersTypes::STATUS_TRUE
				)
			) {
				$m['status'] = ['message' => 'O valora tribuído para o campo status, é inválido.'];
			}
		}
		if (isset($r->order) && $r->order != "") {
			if (!is_numeric($r->order)) {
				$m['order'] = ['message' => 'O valor atribuído para o campo ordem, é inválido.'];
			}
		}
		if (!isset($r->expire) || $r->expire == "") {
			$m['status'] = ['message' => 'O campo expirar é obrigatório.'];
		} else {
			if (
				!is_numeric($r->expire) ||
				(
					$r->expire != BannersTypes::EXPIRE_FALSE &&
					$r->expire != BannersTypes::EXPIRE_TRUE
				)
			) {
				$m['expire'] = ['message' => 'O valor atribuído para o campo expire, é inválido.'];
			}
		}
		if ($r->expire == BannersTypes::EXPIRE_TRUE) {
			if (empty($r->date_start)) {
				$m['date_start'] = ['message' => 'O campo da data de início de exibição, é obrigatório.'];
			}
			if (empty($r->date_end)) {
				$m['date_end'] = ['message' => 'O campo da data de fim de exibição, é obrigatório.'];
			}
			if (!empty($r->date_start) && !empty($r->date_end)) {
				$valid = false;
				if (!Utils::ValidateDate($r->date_start)) {
					$m['date_start'] = ['message' => 'O valor atribuído para o campo data de inicío de exibição, é inválido.'];
				} else $valid = true;
				if (!Utils::ValidateDate($r->date_end)) {
					$m['date_end'] = ['message' => 'O valora tribuído para o campo data de fim de exibição, é inválido.'];
					$valid = false;
				}

				if ($valid) {
					$dateStart = new \DateTime($r->date_start);
					$dateEnd = new \DateTime($r->date_end);

					if ($dateStart >= $dateEnd) {
						$m['date_end'] = ['message' => 'A data de fim de exibição deve ser maior que a data de início de exibição.'];
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
	protected static function validationUpdateType($r) {
		$m = [];
		if(empty($r->id)) {
			$m['type'] = ['message' => 'É necessário selecionar o tipo de banner que deseja editar.'];
		} else {
			$tb = BannersTypes::find($r->id);
			if(!$tb) {
				$m['type'] = ['message' => 'O tipo de banner selecionado, não existe.'];
			}
		}
		if(count($m) == 0) {
			if (empty($r->title)) {
				$m['title'] = ['message' => 'O campo título, é obrigatório.'];
			} else {
				$bt = BannersTypes::where([
					['title', '=', $r->title],
					['id', '!=', $r->id]
				]);
				if ($bt->count() > 0) {
					$m['title'] = ['message' => 'O valor atribuído para o campo title, já existe.'];
				}
			}
			if (!isset($r->status) || $r->status == "") {
				$m['status'] = ['message' => 'O campo status é obrigatório.'];
			} else {
				if (
					!is_numeric($r->status) ||
					(
						$r->status != BannersTypes::STATUS_FALSE &&
						$r->status != BannersTypes::STATUS_TRUE
					)
				) {
					$m['status'] = ['message' => 'O valor atribuído para o campo status, é inválido.'];
				}
			}
			if (!isset($r->expire) || $r->expire == "") {
				$m['expire'] = ['message' => 'O campo expirar, é obrigatório'];
			} else {
				if (
					!is_numeric($r->expire) ||
					(
						$r->expire != BannersTypes::EXPIRE_FALSE &&
						$r->expire != BannersTypes::EXPIRE_TRUE
					)
				) {
					$m['expire'] = ['message' => 'O valor atribuído para o campo expirar, é inválido.'];
				}
			}
			if ($r->expire == BannersTypes::EXPIRE_TRUE) {
				if (empty($r->date_start)) {
					$m['date_start'] = ['mmessage' => 'O campo da data de início de exibição, é obrigatório.'];
				}
				if (empty($r->date_end)) {
					$m['date_end'] = ['message' => 'O campo da data de fim de exibição, é obrigatório.'];
				}
				if (!empty($r->date_start) && !empty($r->date_end)) {
					$valid = false;
					if (!Utils::ValidateDate($r->date_start)) {
						$m['date_start'] = ['message' => 'O valor atribuído para o campo data de inicío de exibição, é inválido.'];
					} else $valid = true;
					if (!Utils::ValidateDate($r->date_end)) {
						$m['date_end'] = ['message' => 'O valora tribuído para o campo data de fim de exibição, é inválido.'];
						$valid = false;
					}

					if ($valid) {
						$dateStart = new \DateTime($r->date_start);
						$dateEnd = new \DateTime($r->date_end);

						if ($dateStart >= $dateEnd) {
							$m['date_end'] = ['message' => 'A data de fim de exibição deve ser maior que a data de início de exibição.'];
						}
					}
				}
			}
			if(isset($r->order)) {
				if(!is_numeric($r->order)) {
					$m['order'] = ['message' => 'O valor atribúido para o campo order, é inválido.'];
				}
			}
		}

		return $m;
	}

	/**
	 * @param $r
	 * @return array
	 */
	protected static function validationRemoveTypes($r) {
		$m = [];
		if(count($r->types) == 0) {
			$m['type'] = ['message' => 'É necessário selecionar o tipo que deseja apagar.'];
		} else {
			foreach($r->types as $t) {
				if(!BannersTypes::has($t)) {
					$m['type'] = ['message' => 'O tipo "' . $t . '", não foi encontrado.'];
				}
			}
		}
		return $m;
	}

	/**
	 * @param $r
	 * @return array
	 */
	protected static function validationlistTypes ($r) {
		$m = [];
		$orders = ['asc', 'ASC', 'desc', 'DESC'];
		$columns = ['id', 'status', 'order', 'expire', 'date_start', 'date_end', 'created_at', 'updated_at'];

		if (isset($r->activeds) && !is_numeric($r->activeds)) {
			$m['activeds'] = ['message' => 'O valor atribúido para o campo activeds, é inválido.'];
		}
		if (isset($r->status)) {
			if (
				!is_numeric($r->status) ||
				(
					$r->status != BannersTypes::STATUS_FALSE &&
					$r->status != BannersTypes::STATUS_TRUE
				)
			) {
				$m['status'] = ['message' => 'O valor atribuído para o campo status, é inválido.'];
			}
		}
		if (isset($r->orderBy) && !in_array($r->orderBy, $orders)) {
			$m['orderBy'] = ['message' => 'O valor atribuído para o campo orderBy, é inválido.'];
		}
		if (isset($r->orderColumn) && !in_array($r->orderColumn, $columns)) {
			$m['orderColumn'] = ['message' => 'O valor atriuído para o campo orderColumns, é inválido.'];
		}
		if (isset($r->limit) && !is_numeric($r->limit)) {
			$m['limit'] = ['message' => 'O valor atribuído para o campo limit, é inválido.'];
		}
		if (isset($r->page) && !is_numeric($r->page)) {
			$m['page'] = ['message' => 'O valor atribuído para o campo page, é inválido.'];
		}

		return $m;
	}

	/**
	 * @param $r
	 * @return array
	 */
	protected static function validationBannersByTypes($r) {
		if(empty($r->type)) {
			$m['type'] = ['message' => 'É necessário informar o tipo do banner que deseja listar.'];
		} else {
			if(!BannersTypes::has($r->type)) {
				$m['type'] = ['message' => 'O tipo do banner selecionado, não existe.'];
			} else {
				$m = BannersController::validationListBanners($r);
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
	protected function validation ($r, \Closure $success, \Closure $error) {
		$m = [];
		$e = new \Exception();
		$call = $e->getTrace()[1]['function'];

		switch ($call) {
			case 'newType':
				$m = self::validationNewType($r);
				break;
			case 'updateType':
				$m = self::validationUpdateType($r);
				break;
			case 'removeTypes':
				$m = self::validationRemoveTypes($r);
				break;
			case 'listTypes' :
				$m = self::validationListTypes($r);
				break;
			case 'listBannersByTypes' :
				$m = self::validationBannersByTypes($r);
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
	public function newType (Request $request) {
		return $this->validation($request, function () use ($request) {
			return BannersTypes::add($request, function ($data) {
				return \Response::json([
					'success' => [
						'message' => 'Tipo de banner adicionado com sucesso.',
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
	public function updateType (Request $request) {
		return $this->validation($request, function () use ($request) {
			return BannersTypes::edit($request, function($data) {
				return \Response::json([
					'success' => [
						'message' => 'Tipo de banner alterado com sucesso.',
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
	public function removeTypes (Request $request) {
		return $this->validation($request, function() use ($request) {
			return BannersTypes::remove($request, function($data) {
				return \Response::json([
					'success' => [
						'message' => 'Tipos de banners apagados com sucesso.',
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
	public function listTypes (Request $request) {
		return $this->validation($request, function () use ($request) {
			return BannersTypes::lists($request, function ($data) {
				return \Response::json([
					'success' => [
						'message' => 'Dados retornados com sucesso.',
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
						'message' => 'Erro interno, tente novamente mais tarde.'
					]
				], 500);
			});
		}, function ($m) {
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
	public function listBannersByTypes (Request $request) {
		return $this->validation($request, function() use ($request) {
			return BannersTypes::listBanners($request, function($data) {
				return \Response::json([
					'success' => [
						'message' => 'Banners retornados com sucesso.',
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
}