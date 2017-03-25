<?php

namespace App\Http\Controllers\Website;

use App\Libraries\Utils;
use App\Models\Website\Banners;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BannersController extends Controller {

	/**
	 * @param $title
	 * @param null $id
	 * @return array
	 */
	protected static function validateTitle ($title, $id = null) {
		$m = [];
		if (empty($title)) {
			$m = ['message' => 'O campo titulo é obrigatório.'];
		} else {
			$Banners = Banners::where('title', '=', $title);
			if ($id)
				$Banners->where('id', '!=', $id);

			if ($Banners->count() > 0) {
				$m = ['message' => 'O título informado já está em uso.'];
			}
		}
		return $m;
	}

	/**
	 * @param $r
	 * @return array
	 */
	protected static function validateDates ($r) {
		$m = [];
		if ($r->expire == Banners::EXPIRE_TRUE) {
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
	protected static function simpleValidation ($r) {
		$m = [];

		if (!empty($r->expire)) {
			if (
				!is_numeric($r->expire) ||
				(
					$r->expire != Banners::EXPIRE_FALSE &&
					$r->expire != Banners::EXPIRE_TRUE
				)
			) {
				$m['expire'] = ['message' => 'O valor atribuído para o campo expirar, é inválido.'];
			}
		}
		if (!empty($r->order)) {
			if (!is_numeric($r->order)) {
				$m['order'] = ['message' => 'O valor atribuído para o campo ordem, é inválido.'];
			}
		}
		if (!empty($r->target)) {
			if (
				!is_numeric($r->target) ||
				(
					$r->target != Banners::TARGET_SELF &&
					$r->target != Banners::TARGET_BLANK
				)
			) {
				$m['target'] = ['message' => 'O valor atribuído para o campo target, é inválido.'];
			}
		}
		if (!empty($r->status)) {
			if (
				!is_numeric($r->status) ||
				(
					$r->status != Banners::STATUS_FALSE &&
					$r->status != Banners::STATUS_TRUE
				)
			) {
				$m['status'] = ['message' => 'O valor atribuído para o campo status, é inválido.'];
			}
		}

		return $m;
	}

	/**
	 * @param $r
	 * @return array
	 */
	protected static function validationNewBanner ($r) {
		$m = [];
		if ($title = self::validateTitle($r->title)) {
			$m['title'] = $title;
		}
		if ($messages = self::simpleValidation($r)) {
			foreach ($messages as $k => $v) {
				$m[$k] = $v['message'];
			}
		}
		if ($messages = self::validateDates($r)) {
			foreach ($messages as $k => $v) {
				$m[$k] = $v['message'];
			}
		}

		return $m;
	}

	/**
	 * @param $r
	 * @return array
	 */
	public static function validationUpdateBanner ($r) {
		$m = [];

		if (empty($r->id)) {
			$m['banner'] = ['message' => 'É necessário selecionar o banner que deseja alterar.'];
		} else {
			if(!Banners::has($r->id)) {
				$m['banner'] = ['message' => 'O banner selecionado, não existe.'];
			} else {
				if ($title = self::validateTitle($r->title, $r->id)) {
					$m['title'] = $title;
				}
				if ($messages = self::simpleValidation($r)) {
					foreach ($messages as $k => $v) {
						$m[$k] = $v['message'];
					}
				}
				if ($messages = self::validateDates($r)) {
					foreach ($messages as $k => $v) {
						$m[$k] = $v['message'];
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
	public static function validationListBanners ($r) {
		$m = [];
		$orders = ['asc', 'ASC', 'desc', 'DESC'];
		$columns = ['id', 'title', 'expire', 'date_start', 'date_end', 'order', 'created_at', 'updated_at'];
		if (!empty($r->orderBy)) {
			if (!in_array($r->orderBy, $orders)) {
				$m['orderBy'] = ['message' => 'O valor atribuído para o campo orderBy, é inválido.'];
			}
		}
		if (!empty($r->orderColumn)) {
			if (!in_array($r->orderColumn, $columns)) {
				$m['orderColumn'] = ['message' => 'O valor atribuído para o campo orderColumn, é inválido.'];
			}
		}
		if (isset($r->activeds) && $r->activeds != "") {
			if (!is_numeric($r->activeds)) {
				$m['activeds'] = ['message' => 'O valora tribuído para o campo activeds, é inválido'];
			}
		}
		if (isset($r->status) && $r->status != "") {
			if (
				!is_numeric($r->status) ||
				(
					$r->status != Banners::STATUS_FALSE &&
					$r->status != Banners::STATUS_TRUE
				)
			) {
				$m['status'] = ['message' => 'O valor atribuído para o campo status, é inválido.'];
			}
		}
		if (isset($r->limit) && $r->limit != "") {
			if (!is_numeric($r->limit)) {
				$m['limit'] = ['message' => 'O valor atribuído para o campo limit, é inválido.'];
			}
		}
		if (isset($r->page) && $r->page != "") {
			if (!is_numeric($r->page)) {
				$m['page'] = ['message' => 'O valor atribuído para o campo page, é inválido.'];
			}
		}
		return $m;
	}

	/**
	 * @param $r
	 * @return array
	 */
	public static function validationRemoveBanners ($r) {
		$m = [];
		if(!isset($r->banners) || count($r->banners) == 0) {
			$m['banners'] = ['message' => 'É necessário selecionar os banners que deseja apagar.'];
		} else {
			foreach($r->banners as $b) {
				if(!Banners::has($b)) {
					$m['banners'] = ['message' => 'O banner "' . $b . '", não existe.'];
					break;
				}
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
	private function validation ($r, \Closure $success, \Closure $error) {
		$m = [];
		$e = new \Exception();
		$call = $e->getTrace()[1]['function'];

		switch ($call) {
			case 'newBanner' :
				$m = self::validationNewBanner($r);
				break;
			case 'updateBanner' :
				$m = self::validationUpdateBanner($r);
				break;
			case 'removeBanners' :
				$m = self::validationRemoveBanners($r);
				break;
			case 'listBanners' :
				$m = self::validationListBanners($r);
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
	public function newBanner (Request $request) {
		return $this->validation($request, function () use ($request) {
			return Banners::add($request, function ($data) {
				return \Response::json([
					'success' => [
						'message' => 'Banner adicionado com sucesso.',
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
	public function updateBanner (Request $request) {
		return $this->validation($request, function () use ($request) {
			return Banners::edit($request, function ($data) {
				return \Response::json([
					'succcess' => [
						'message' => 'Banner editado com sucesso.',
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
	public function removeBanners (Request $request) {
		return $this->validation($request, function() use ($request) {
			return Banners::remove($request, function($data) {
				return \Response::json([
					'success' => [
						'message' => 'Banners apagados com sucesso.',
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
	public function listBanners (Request $request) {
		return $this->validation($request, function () use ($request) {
			return Banners::lists($request, function($data) {
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
}
