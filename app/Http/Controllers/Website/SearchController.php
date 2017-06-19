<?php

namespace App\Http\Controllers\Website;

use App\Models\Store\Products;
use App\Models\Website\Pages;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SearchController extends Controller {
	/**
	 * @param $request
	 * @return mixed
	 */
	private function searchProducts($request) {
		return Products::search($request, function($data) {
			return $data;
		}, function($e) {
			throw new \Exception($e->getMessage(), 500, $e);
		});
	}

	/**
	 * @param $request
	 * @return mixed
	 */
	private function searchPages($request) {
		return Pages::search($request, function($data) {
			return $data;
		}, function($e) {
			throw new \Exception($e->getMessage(), 500, $e);
		});
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function searchAll(Request $request) {
		try {
			$data = [];
			$data['products'] = [];
			$data['pages'] = [];
			$data['products'] = $this->searchProducts($request);
			$data['pages'] = $this->searchPages($request);

			return \Response::json([
				'success' => [
					'message' => 'Busca realizada com sucesso.',
					'data' => $data
				]
			], 200);
		} catch (\Exception $e) {
			return \Response::json([
				'error' => [
					'message' => 'Erro interno ao realizar a busca.',
					'internal' => [
						'message' => $e->getMessage(),
						'file' => $e->getFile(),
						'line' => $e->getLine()
					]
				]
			], 500);
		}
	}
}