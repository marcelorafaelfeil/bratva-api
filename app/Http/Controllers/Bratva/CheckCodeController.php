<?php

namespace App\Http\Controllers\Bratva;

use App\Models\Bratva\Codes;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CheckCodeController extends Controller {
	private static function validationNewCode ($r) {
		$m = [];
		if (empty($r->code)) {
			$m['code'] = ['message' => 'O campo código é obrigatório.'];
		} else {
			if (Codes::has($r->code)) {
				$m['code'] = ['message' => 'O código que está tentando cadastrar, já existe.'];
			}
		}

		return $m;
	}

	private function validationRemoveCodes($r) {
		$m = [];

		if(empty($r->codes) || count($r->codes) == 0) {
			$m['codes'] = ['message' => 'É necessário selecionar os códigos que deseja remover.'];
		} else {
			foreach($r->codes as $c) {
				if(!Codes::has($c)) {
					$m['codes'] = ['message' => 'O código "' . $c . '" não existe.'];
				}
			}
		}

		return $m;
	}

	private function validation ($r, \Closure $success, \Closure $error) {
		$m = [];
		$e = new \Exception();
		$call = $e->getTrace()[1]['function'];

		switch ($call) {
			case 'newCode':
				$m = self::validationNewCode($r);
				break;
			case 'removeCodes':
				$m = self::validationRemoveCodes($r);
				break;
		}

		if (count($m) > 0) {
			return $error($m);
		} else {
			return $success();
		}
	}

	public function newCode (Request $r) {
		return $this->validation($r, function () use ($r) {
			return Codes::add($r, function ($data) {
				return \Response::json([
					'success' => [
						'message' => 'Código adicionado com sucesso.',
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

	public function removeCodes (Request $r) {
		return $this->validation($r, function () use ($r) {
			return Codes::remove($r->codes, function ($data) {
				return \Response::json([
					'success' => [
						'message' => 'Códigos apagado com sucesso.',
						'data' => $data
					]
				],200);
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
}
