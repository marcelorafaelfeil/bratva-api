<?php

namespace App\Http\Controllers\Auth;

use App\Models\Users;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
	private static function validationAuth ($r) {
		$m = [];

		if(empty($r->login)) {
			$m['user'] = 'É necessário informar o usuário.';
		}

		if(empty($r->pass)) {
			$m['pass'] = 'É necessário informar uma senha.';
		}

		if(count($m) == 0) {
			if(!Users::verifyCredentials($r->login, $r->pass))
				$m['user'] = 'Login ou senha incorretos.';
		}

		return $m;
	}

	private function validation ($r, \Closure $success, \Closure $error) {
		$m = [];

		$e = new \Exception();
		$call = $e->getTrace()[1]['function'];

		switch ($call) {
			case 'Authentication':
				$m = self::validationAuth($r);
				break;
		}

		if(count($m) > 0)
			return $error($m);
		return $success();
	}

	public function Authentication (Request $r) {
		return $this->validation($r, function () use ($r) {
			return \Response::json([
				'success' => [
					'message' => 'Autenticado com sucesso.',
					'token' => Users::generateJWT(['login' => $r->login])
				]
			]);
		}, function ($m) {
			return \Response::json([
				'errors' => [
					'messages' => $m
				]
			],400);
		});
	}
}
