<?php

namespace App\Http\Controllers\Bratva;

use App\Http\Controllers\Generic\UploadsController;
use App\Models\Bratva\Codes;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class CheckCodeController extends UploadsController {
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

	private static function validationRemoveCodes ($r) {
		$m = [];

		if (empty($r->codes) || count($r->codes) == 0) {
			$m['codes'] = ['message' => 'É necessário selecionar os códigos que deseja remover.'];
		} else {
			foreach ($r->codes as $c) {
				if (!Codes::has($c)) {
					$m['codes'] = ['message' => 'O código "' . $c . '" não existe.'];
				}
			}
		}

		return $m;
	}

	private static function validationImportCodes ($r) {
		$m = [];

		$extensions = ['xlsx', 'xls', 'csv'];

		if (!$r->file('codes')) {
			$m['file'] = ['message' => 'É necessário selecionar o arquivo de códigos.'];
		} else {
			if (!in_array($r->file('codes')->getClientOriginalExtension(), $extensions)) {
				$m['file'] = ['message' => 'A extensão do arquivo é inválida.'];
			}
		}

		return $m;
	}

	private static function validationCheckCode ($r) {
		$m = [];

		if(empty($r->code)) {
			$m['code'] = ['message' => 'O campo código é obrigatório.'];
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
			case 'importCodes':
				$m = self::validationImportCodes($r);
				break;
			case 'checkCode':
				$m = self::validationCheckCode($r);
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

	public function importCodes (Request $r) {
		return $this->validation($r, function () use ($r) {
			$this->setDirectory($this->getBase() . '/codes/');
			return $this->Upload($r->file('codes'), function ($name) {
				$errors = [];
				$data = [];

				$excel = Excel::load($this->getDirectory().$name);
				$excel->noHeading();
				foreach($excel->toArray()[0] as $e) {
					if(!Codes::has($e[0])) {
						try {
							$data[]=['code'=>$e[0]];
						} catch (\Exception $exp) {
							array_push($errors,[
								'message' => 'Erro ao cadastrar o código "'.$e[0].'".'
							]);
						}
					} else {
						array_push($errors,[
							'meesage' => 'O código "'.$e[0].'", já existe.'
						]);
					}
				}

				if(count($data) > 0) {
					Codes::insert($data);
				}

				return \Response::json([
					'success' => [
						'message' => 'Códigos processados com sucesso.',
						'errors' => $errors,
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

	public function checkCode (Request $r) {
		return $this->validation($r, function() use ($r) {
			if(Codes::has($r->code)) {
				return \Response::json([
					'success' => [
						'message' => 'Produto original.',
						'valid' => 'true'
					]
				]);
			} else {
				return \Response::json([
					'success' => [
						'message' => 'Este produto não é original.',
						'valid' => 'false'
					]
				]);
			}
		}, function($m) {
			return \Response::json([
				'errors' => [
					'messages' => $m
				]
			]);
		});
	}
}
