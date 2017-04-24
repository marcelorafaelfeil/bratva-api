<?php

namespace App\Http\Controllers\Generic;

use App\Models\Store\Products;
use App\Models\Store\Brands;
use App\Models\Website\Banners;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Libraries\Utils;
use App\Models\Generic\Images;

class ImagesControllers extends UploadsController
{
	private $MAX_SIZE = '2048';     // kb - null = unlimited
	private $MAX_WIDTH = '';        // px - null = unlimited
	private $MAX_HEIGHT = '';       // px - null = unlimited
	private $MIN_WIDTH = '';        // px - null = unlimited
	private $MIN_HEIGHT = '';       // px - null = unlimited
	private $EXTENSIONS = ['jpg', 'jpeg', 'png'];

	private $PATHS = [
		'products' => '/products/',
		'brands' => '/brands/',
		'banners' => '/banners/'
	];

	private $section;
	private $key;


	/**
	 * @return bool
	 */
	private function hasItem() {
		$has=false;
		switch($this->section) {
			case 'products' :
				$has = Products::has($this->key);
				break;
			case 'brands':
				$has = Brands::has($this->key);
				break;
			case 'banners':
				$has = Banners::has($this->key);
				break;
		}
		return $has;
	}

	/**
	 * @param $r
	 * @param \Closure $fun
	 * @return mixed
	 */
	private function validation($r, \Closure $fun) {
		$m = [];
		$e = new \Exception();

		// Se o método que chamou, foi o uploadImages, faz validações diferentes
		if($e->getTrace()[1]['function'] == 'uploadImages') {
			if(!self::hasItem()) {
				$m[] = 'A chave não está associada a um item.';
			}
			if(!$r->hasFile('images')) {
				$m[] = 'Não foi encontrado arquivos para upload.';
			} else {
				foreach($r->file('images') as $k => $f) {
					if (!$f->isValid()) {
						$m[] = 'O arquivo "'.$f->getClientOriginalName().'", não é um arquivo válido.';
					}
					if(!in_array($f->extension(),$this->EXTENSIONS)) {
						$m[] = 'O arquivo "'.$f->getClientOriginalName().'", não é um arquivo válido.';
					}
				}
			}
			if(!isset($this->PATHS[$this->section])) {
				$m[] = 'A seção informada, é inválida.';
			}
		} else if($e->getTrace()[1]['function'] == 'removeImages') {
			foreach($r->keys as $k) {
				if(!Images::has($k)) {
					$m[] = 'A imagem de chave '.$k.', não foi encontrada no banco de dados.';
				}
			}
		}
		return $fun($m);
	}

	/**
	 * @param $section
	 */
	private function setSection($section) {
		$this->section = $section;
	}

	/**
	 * @param $k
	 */
	private function setKey($k) {
		$this->key = $k;
	}

	/**
	 * @param $section
	 * @param Request $request
	 * @return mixed
	 */
	public function uploadImages($section, $key, Request $request){
		try {
			$this->setSection($section);
			$this->setKey($key);
			return $this->validation($request, function ($m) use ($section, $key, $request) {
				if (!$m) {
					$this->setDirectory($this->getBase() . $this->PATHS[$section] . Utils::KeyDir($key));
					$count = 0;
					foreach ($request->file('images') as $k => $file) {
						$this->Upload($file, function ($name) use ($section, $key, $k, $request, $count) {

							if(isset($request->idImage[$k]) && !empty($request->idImage[$k])) {
								$img = Images::find($request->idImage[$k]);
								$img->src = env('APP_IMAGES_URL') . $this->PATHS[$section] . $key . '/' . $name;
								$img->legend = isset($request->legend[$k]) ? $request->legend[$k] : '';
								$img->save();
							} else {
								$img = Images::create([
									'src' => env('APP_IMAGES_URL') . $this->PATHS[$section] . $key . '/' . $name,
									'legend' => isset($request->legend[$k]) ? $request->legend[$k] : '',
									'featured' => isset($request->featured[$k]) ? $request->featured[$k] : Images::FEATURED_FALSE
								]);

								Images::createRelation($section, $key, $img);
							}
						}, function ($e) use ($file) {
							throw new \Exception('Ocorreu um erro ao tentar salvar a imagem ' . $file->getClientOriginalName() . '.', 0, $e);
						});
					}
					return \Response::json([
						'success' => [
							'message' => 'Imagens salvas com sucesso.'
						]
					]);
				} else {
					return \Response::json([
						'errors' => [
							'messages' => $m
						]
					]);
				}
			});
		} catch (\Exception $e) {
			$prev = $e->getPrevious();
			return \Response::json([
				'error' => [
					'internal' => [
						'message' => $prev->getMessage(),
						'file' => $prev->getFile(),
						'line' => $prev->getLine()
					],
					'message' => 'Erro interno. Tente novamente mais tarde.'
				]
			], 500);
		}
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function removeImages(Request $request) {
		try {
			return $this->validation($request, function($m) use ($request) {
				if(!$m) {
					$items=[];
					foreach($request->keys as $k) {
						$item = Images::remove($k, function ($data) use ($k) {
							return $data;
						}, function ($e) {
							throw new \Exception('Ocorreu um erro ao tentar apagar a imagem da chave '.$k.'.', 0, $e);
						});

						array_push($items, $item);
					}

					return \Response::json([
						'success' => [
							'message' => 'Imagen(s) apagada(s) com sucesso.',
							'data' => $items
						]
					]);
				} else {
					return \Response::json([
						'errors' => [
							'messages' => $m
						]
					], 400);
				}
			});
		} catch (\Exception $e) {
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
		}
	}
}