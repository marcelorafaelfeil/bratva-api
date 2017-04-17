<?php

namespace App\Http\Controllers\Generic;

use App\Models\Generic\Emails;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EmailsController extends Controller {

	/**
	 * @param $r
	 * @return array
	 */
	private function validateListEmails($r) {
		$m = [];

		$orders = ['asc', 'ASC', 'desc', 'DESC'];
		$columns = ['id', 'email'];

		if(isset($r->orderBy) && !in_array($r->orderBy, $orders)) {
			$m['order'] = ['message' => 'O valor atribuído para o campo orderBy, é inválido.'];
		}

		if(isset($r->orderColumn) && !in_array($r->orderColumn, $columns)) {
			$m['column'] = ['message' => 'O valor atribuído para o campo orderColumn, é inválido.'];
		}

		if(isset($r->limit) && !is_numeric($r->limit)) {
			$m['limit'] = ['message' => 'O valor atribuído para o campo limite, é inválido.'];
		}
		if(isset($r->page) && !is_numeric($r->page)) {
			$m['page'] = ['message' => 'O valor atribuído para o campo página, é inválido.'];
		}

		if(isset($r->verified) && $r->verified != "") {
			if(
				!is_numeric($r->verified) ||
				(
					$r->verified != Emails::VERIFIED_TRUE &&
					$r->verified != Emails::VERIFIED_FALSE
				)
			) {
				$m['verified'] = ['message' => 'O valor atribuído ao campo verificado, é inválido.'];
			}
		}

		return $m;
	}

	/**
	 * @param $r
	 * @return array
	 */
	private function validateNewEmail($r) {
		$m = [];

		if(empty($r->email)) {
			$m['email'] = ['message' => 'O campo e-mail é obrigatório.'];
		} else {
			if(Emails::has($r->email)) {
				$m['email'] = ['message' => 'O e-mail informado já existe.'];
			}
		}

		return $m;
	}

	/**
	 * @param $r
	 * @return array
	 */
	private function validateUpdateEmail($r) {
		$m = [];

		if(!isset($r->email) || empty($r->email)) {
			$m['email'] = ['message' => 'O campo e-mail é obrigatório.'];
		} else {
			if(Emails::has($r->email, $r->id)) {
				$m['email'] = ['message' => 'O e-mail informado já existe.'];
			}
		}

		if(isset($r->verified) && $r->verified != "") {
			if(
				!is_numeric($r->verified) ||
				(
					$r->verified != Emails::VERIFIED_TRUE &&
					$r->verified != Emails::VERIFIED_FALSE
				)
			) {
				$m['verified'] = ['message' => 'O valor atribuído ao campo verificado, é inválido.'];
			}
		}

		return $m;
	}

	/**
	 * @param $r
	 * @return array
	 */
	private function validateRemoveEmails($r) {
		$m = [];
		if(!isset($r->emails) || count($r->emails) == 0) {
			$m['emails'] = ['message' => 'É necessário selecionar os e-mails que deseja apagar.'];
		} else {
			foreach($r->emails as $e) {
				if(!Emails::has($e)) {
					$m['emails'] = ['message' => 'O e-mail "'.$e.'" não existe.'];
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
	private function validation($r, \Closure $success, \Closure $error) {
		$m=[];

		$e = new \Exception();
		$call = $e->getTrace()[1]['function'];

		switch($call) {
			case 'listEmails':
				$m = self::validateListEmails($r);
				break;
			case 'newEmail' :
				$m = self::validateNewEmail($r);
				break;
			case 'updateEmail' :
				$m = self::validateUpdateEmail($r);
				break;
			case 'removeEmail' :
				$m = self::validateRemoveEmails($r);
				break;
		}

		if(count($m) > 0) {
			return $error($m);
		}
		return $success();
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function listEmails(Request $request) {
		return $this->validation($request, function() use ($request) {
			return Emails::lists($request, function($data) {
				return \Response::json([
					'success' => [
						'message' => 'Emails retornados com sucesso.',
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
					'messages' => $m
				]
			], 400);
		});
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function newEmail(Request $request) {
		return $this->validation($request, function() use ($request) {
			return Emails::add($request, function($data) {
				return \Response::json([
					'success' => [
						'message' => 'E-mail adicionado com sucesso',
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
					'messages' => $m
				]
			], 400);
		});
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function updateEmail(Request $request) {
		return $this->validation($request, function() use ($request) {
			return Emails::edit($request, function($data) {
				return \Response::json([
					'success' => [
						'message' => 'E-mail editado com sucesso.',
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
			], 400);
		});
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function removeEmails(Request $request) {
		return $this->validation($request, function() use ($request) {
			return Emails::remove($request, function($data) {
				return \Response::json([
					'success' => [
						'message' => 'Emails apagados com sucesso.',
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
					'messages' => $m
				]
			], 400);
		});
	}
}
