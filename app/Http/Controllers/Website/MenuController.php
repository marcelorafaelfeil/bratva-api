<?php

namespace App\Http\Controllers\Website;

use App\Models\Website\Menu;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MenuController extends Controller {
	protected static function simpleValidation($r) {
		$m = [];
		if(empty($r->title)) {
			$m['title'] = ['message' => 'O campo título é obrigatório.'];
		}
		if($r->type == "") {
			$m['type'] = ['message' => 'O campo tipo é obrigatório.'];
		} else {
			if(
				!is_numeric($r->type) ||
				(
					$r->type != Menu::TYPE_TOP &&
					$r->type != Menu::TYPE_FOOTER
				)
			) {
				$m['type'] = ['message' => 'O valor atribuído para o campo tipo, é inválido.'];
			}
		}
		if(isset($r->status)) {
			if(
				!is_numeric($r->status) ||
				(
					$r->status != Menu::STATUS_FALSE &&
					$r->status != Menu::STATUS_TRUE
				)
			) {
				$m['status'] = ['message' => 'O valor atribuído para o campo status, é inválido'];
			}
		}
		if(isset($r->target)) {
			if(
				!is_numeric($r->target) ||
				(
					$r->target != Menu::TARGET_SELF &&
					$r->target != Menu::TARGET_BLANK
				)
			) {
				$m['target'] = ['message' => 'O valor atribuído para o campo target, é inválido.'];
			}
		}
		return $m;
	}

	protected static function validateNewMenu($r) {
		$m = [];

		if($messages = self::simpleValidation($r)) {
			foreach($messages as $k => $v) {
				$m[$k] = $v;
			}
		}

		return $m;
	}

	protected static function validateUpdateMenu($r) {
		$m = [];

		if(empty($r->id)) {
			$m['menu'] = ['message' => 'É necessário selecionar o menu que deseja editar.'];
		} else {
			if ($messages = self::simpleValidation($r)) {
				foreach ($messages as $k => $v) {
					$m[$k] = $v;
				}
			}
		}

		return $m;
	}

	protected static function validateRemoveMenus($r) {
		$m = [];

		if(count($r->menus) == 0) {
			$m['menu'] = ['message' => 'É necessário selecionar os menus que deseja apagar.'];
		} else {
			foreach($r->menus as $code) {
				if(!Menu::has($code)) {
					$m['menu'] = ['messages' => 'O menu "' . $code . '", não foi encontrado.'];
					break;
				}
			}
		}

		return $m;
	}

	protected static function validateListMenus($r) {
		$m = [];
		if(isset($r->type)) {
			if(
				!is_numeric($r->type) ||
				(
					$r->type != Menu::TYPE_TOP &&
					$r->type != Menu::TYPE_FOOTER
				)
			) {
				$m['type'] = ['message' => 'O valor atribuído para o campo tipo, é inválido.'];
			}
		}
		if(isset($r->status)) {
			if(
				!is_numeric($r->status) ||
				(
					$r->status != Menu::STATUS_FALSE &&
					$r->status != Menu::STATUS_TRUE
				)
			) {
				$m['status'] = ['message' => 'O valor atribuído para o campo status, é inválido.'];
			}
		}
		return $m;
	}

	protected function validation($r, \Closure $success, \Closure $error) {
		$m = [];

		$e = new \Exception();
		$call = $e->getTrace()[1]['function'];

		switch($call) {
			case 'newMenu':
				$m = self::validateNewMenu($r);
				break;
			case 'updateMenu':
				$m = self::validateUpdateMenu($r);
				break;
			case 'removeMenus':
				$m = self::validateRemoveMenus($r);
				break;
			case 'listMenus':
				$m = self::validateListMenus($r);
				break;
		}

		if(count($m) == 0) {
			return $success();
		} else {
			return $error($m);
		}
	}

	public function newMenu(Request $r) {
		return $this->validation($r, function() use ($r) {
			return Menu::add($r, function($data) {
				return \Response::json([
					'success' => [
						'message' => 'Menu adicionado com sucesso.',
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
		}, function($m) {
			return \Response::json([
				'errors' => [
					'messages' => $m
				]
			], 400);
		});
	}

	public function updateMenu(Request $r) {
		return $this->validation($r, function() use ($r) {
			return Menu::edit($r, function ($data) {
				return \Response::json([
					'success' => [
						'message' => 'Menu editado com sucesso.',
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
		}, function($m) {
			return \Response::json([
				'errors' => [
					'messages' => $m
				]
			], 400);
		});
	}

	public function removeMenus(Request $r) {
		return $this->validation($r, function() use ($r) {
			return Menu::remove($r, function($data) {
				return \Response::json([
					'success' => [
						'message' => 'Menu removido com sucesso.',
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

	public function listMenus(Request $r) {
		return $this->validation($r, function() use ($r) {
			return Menu::lists($r, function ($data) {
				return \Response::json([
					'success' => [
						'message' => 'Menus retornados com sucesso.',
						'data' => $data
					]
				], 200);
			}, function($e) {
				return \Response::json([
					'errors' => [
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
}
