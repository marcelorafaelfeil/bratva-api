<?php

namespace App\Http\Controllers\Auth;

use App\Models\Users;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use League\Flysystem\Exception;

class RegisterController extends Controller
{

	private function validationAdd($r) {
		$m = [];

		if(empty($r->login)) {
			$m['user'] = 'É necessário informar o usuário.';
		} else {
			if(Users::hasUser($r->login))
				$m['user'] = 'O usuário informado já existe.';
		}

		if(!isset($r->pass) || $r->pass === '') {
			$m['pass'] = 'É necessário criar uma senha.';
		}

		return $m;
	}

	private function validationUpdate($r) {
		$m = [];

		if(empty($r->login)) {
			$m['user'] = 'É necessário informar o usuário.';
		} else {
			if(Users::hasUser($r->login, $r->id))
				$m['user'] = 'O usuário informado já existe.';
		}

		if(!isset($r->pass) || $r->pass === '') {
			$m['pass'] = 'É necessário criar uma senha.';
		}

		return $m;
	}

	private function validation($r, \Closure $success, \Closure $error) {
		$m = [];

		$e = new Exception();
		$call = $e->getTrace()[1]['function'];

		switch($call) {
			case 'add':
				$m = self::validationAdd($r);
				break;
			case 'update':
				$m = self::validationUpdate($r);
				break;
		}

		if(count($m) > 0)
			return $error($m);
		return $success();
	}

	public function add(Request $r) {
		return $this->validation($r, function() use($r) {
			return Users::add($r, function($data) {
				return \Response::json([
					'success' => [
						'message' => 'Usuário criado com sucesso.',
						'data' => $data
					]
				], 200);
			}, function($e) {
				return \Response::json([
					'error' => [
						'message' => 'Erro interno. Tente novamente mais tarde.',
						'internal' => [
							'message' => $e->getMessage(),
							'line' => $e->getLine(),
							'file' => $e->getLine()
						]
					]
				], 400);
			});
		}, function($m) {
			return \Response::json([
				'errors' => [
					'messages' => $m
				]
			], 400);
		});
	}

	public function edit(Request $r) {
		return $this->validation($r, function() use($r) {
			return Users::edit($r, function($data) {
				return \Response::json([
					'success' => [
						'message' => 'Usuário editado com sucesso.',
						'data' => $data
					]
				], 200);
			}, function($e) {
				return \Response::json([
					'error' => [
						'message' => 'Erro interno. Tente novamente mais tarde.',
						'internal' => [
							'message' => $e->getMessage(),
							'line' => $e->getLine(),
							'file' => $e->getLine()
						]
					]
				], 400);
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
