<?php

namespace App\Http\Controllers\Store;

use App\Models\Store\Currencies;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CurrenciesController extends Controller {

	private function _validationListCurrencies($r) {
		$m = [];
		$columns = ['id', 'name', 'symbol'];
		$orders = ['asc', 'ASC','desc','DESC'];

		if(isset($r->orderBy) && !in_array($r->orderBy, $orders)) {
			$m['order'] = ['message' => 'O valor atribuído para o campo order, é inválido'];
		}
		if(isset($r->orderColumn) && !in_array($r->orderColumn, $columns)) {
			$m['column'] = ['message' => 'O nome da columna é inválido.'];
		}

		return $m;
	}

	private function _validation($r, \Closure $success, \Closure $error) {
		$m = [];
		$e = new \Exception();
		$call = $e->getTrace()[1]['function'];

		switch($call) {
			case 'listCurrencies':
				$m = self::_validationListCurrencies($r);
				break;
		}

		if(count($m) > 0)
			return $error($m);
		return $success();
	}

	public function listCurrencies (Request $request) {
		return $this->_validation($request, function () use ($request) {
			return Currencies::lists($request, function ($data) {
				return \Response::json([
					'success' => [
						'message' => 'Moedas retornadas com sucesso.',
						'data' => $data
					]
				], 200);
			}, function ($e) {
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
		}, function ($m) {
			return \Response::json([
				'errors' => [
					'messages' => $m
				]
			], 400);
		});
	}
}
