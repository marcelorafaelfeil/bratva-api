<?php
/**
 * User: Marcelo Rafael <marcelo.rafael.feil@gmail.com>
 * Date: 16/04/2017
 */

namespace App\Http\Controllers\Website;

use App\Models\Website\Newsletter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NewsletterController extends Controller {

	/**
	 * @param $r
	 * @return array
	 */
	private function validationCreateNewsletter($r){
		$m = [];
		if(!isset($r->email) || empty($r->email)) {
			$m['email'] = ['message' => 'O campo e-mail é obrigatório.'];
		} else {
			if(!filter_var($r->email, FILTER_VALIDATE_EMAIL)) {
				$m['email'] = ['message' => 'O e-mail informado, é inválido.'];
			} else {
				if(Newsletter::hasEmail($r->email)) {
					$m['email'] = ['message' => 'Este e-mail já está cadastrado.'];
				}
			}
		}

		return $m;
	}

	/**
	 * @param $r
	 * @return array
	 */
	private function validationUpdateNewsletter($r) {
		$m = [];

		if(!isset($r->email) || empty($r->email)) {
			$m['email'] = ['message' => 'O campo e-mail é obrigatório.'];
		} else {
			if(!filter_var($r->email, FILTER_VALIDATE_EMAIL)) {
				$m['email'] = ['message' => 'O e-mail informado, é inválido.'];
			} else {
				if(Newsletter::hasEmail($r->email, $r->id)) {
					$m['email'] = ['message' => 'Este e-mail já está cadastrado.'];
				}
			}
		}

		return $m;
	}

	/**
	 * @param $r
	 * @return array
	 */
	private function validationRemoveNewsletter($r) {
		$m = [];

		if(count($r->newsletters) == 0) {
			$m['newsletters'] = ['message' => 'É necessário selecionar os e-mails que deseja remover da lista.'];
		} else {
			foreach($r->newsletters as $n) {
				if(!Newsletter::has($n)) {
					$m['newsletters'] = ['message' => 'O item "'.$n.'" da lista, não foi encontrado.'];
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
			case 'createNewsletter':
				$m = self::validationCreateNewsletter($r);
				break;
			case 'updateNewsletter':
				$m = self::validationUpdateNewsletter($r);
				break;
			case 'removeNewsletters':
				$m = self::validationRemoveNewsletter($r);
				break;
		}

		if(count($m) > 0)
			return $error($m);
		return $success();
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function createNewsletter(Request $request) {
		return $this->validation($request, function() use ($request) {
			return Newsletter::add($request, function($data) {
				return \Response::json([
					'success' => [
						'message' => 'Newsletter criada com sucesso.',
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

	/*public function updateNewsletter(Request $request) {
		return $this->validation($request, function() use ($request) {
			return Newsletter::edit($request, function($data) {
				return \Response::json([
					'success' => [
						'message' => 'Newsletter alterada com sucesso.',
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
	}*/

	/**
	 * @return mixed
	 */
	public function listNewsletters() {
		return Newsletter::lists(function($data) {
			return \Response::json([
				'success' => [
					'message' => 'Newsletters retornadas com sucesso.',
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
			]);
		});
	}

	/**
	 * @param Request $r
	 * @return mixed
	 */
	public function removeNewsletters(Request $r) {
		return $this->validation($r, function() use ($r) {
			return Newsletter::remove($r, function($data) {
				return \Response::json([
					'success' => [
						'message' => 'Newsletters removidas com sucesso.',
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
