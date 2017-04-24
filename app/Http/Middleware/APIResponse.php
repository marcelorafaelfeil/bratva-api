<?php
/**
 * Created by PhpStorm.
 * User: Marcelo Rafael
 * Date: 18/03/2017
 * Time: 17:02
 */

namespace App\Http\Middleware;

use Closure;

class APIResponse
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next) {
		// ALLOW OPTIONS METHOD
		$headers = [
			'Access-Control-Allow-Origin' => '*',
			'Access-Control-Allow-Methods'=> 'HEAD, POST, GET, OPTIONS, PUT, DELETE',
			'Access-Control-Allow-Headers'=> 'Content-Type, Authorization, X-Auth-Token, Origin, X-Requested-With, Accept'
		];
		if($request->getMethod() == "OPTIONS") {
			// The client-side application can set only headers allowed in Access-Control-Allow-Headers
			return \Response::make('OK', 200, $headers);
		}

		$response = $next($request);

		if(method_exists($response,'getData')) {
			foreach ($headers as $key => $value) {
				$response->header($key, $value);
			}
		}
		return $response;
	}
}
