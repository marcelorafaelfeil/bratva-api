<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Users extends Model {
	protected $fillable = [
		'login',
		'pass'
	];
	public $timestamps = false;

	public static function hasUser ($user, $id = '') {
		$u = Users::query();

		if (isset($id) && !empty($id))
			$u->where('id', '!=', $id);
		$u->where('login', '=', $user);

		return ($u->count() > 0);
	}

	public static function verifyCredentials ($user, $p) {
		$u = Users::query();

		$users = $u->where('login', '=', $user)->get();
		$auth = false;
		foreach($users as $u) {
			$pass = Crypt::decrypt($u->pass);
			if($pass === $p) {
				$auth = true;
				break;
			}
		}

		return $auth;
	}

	public static function add ($r, \Closure $success, \Closure $error) {
		try {
			//'senha' => Crypt::encrypt($request->input('pass')),
			$user = Users::create([
				'login' => $r->login,
				'pass' => Crypt::encrypt($r->pass)
			]);

			return $success($user);
		} catch (\Exception $e) {
			return $error($e);
		}
	}

	public static function edit ($r, \Closure $success, \Closure $error) {
		try {
			$user = Users::find($r->id);
			$user->login = $r->login;
			$user->pass = Crypt::encrypt($r->pass);
			$user->save();

			return $success($user);
		} catch (\Exception $e) {
			return $error($e);
		}
	}

	public static function generateJWT ($inf) {
		$header = [
			'alg' => 'HS256',
			'typ' => 'JWT'
		];

		$Iat = new \DateTime();
		$Exp = new \DateTime();

		$Exp->modify('+1 hours');
		$payload = [
			'iss' => env('APP_URL'),
			'iat' => $Iat->getTimestamp(),
			'exp' => $Exp->getTimestamp(),
			'name' => $inf['login'],
			/*'email' => $inf['email'],
			'nivel' => $inf['nivel']*/
		];

		$header = base64_encode(json_encode($header));
		$payload = base64_encode(json_encode($payload));

		$assignature = hash_hmac('sha256', $header . '.' . $payload, env('APP_KEY'), true);
		$assignature = base64_encode($assignature);

		$jwt = $header . '.' . $payload . '.' . $assignature;

		return $jwt;
	}

	public static function getToken () {
		$headers = getallheaders();
		$auth = isset($headers['Authorization']) ? $headers['Authorization'] : '';

		if ($auth) {
			if (preg_match('/\bBearer\b/i', $auth)) {
				$auth = trim(preg_replace('/\bBearer\b/i', '', $auth));
				return $auth;
			}
		}
		return false;
	}

	public static function getJWTPayload () {
		if ($token = self::getToken()) {
			$t = explode('.', $token);
			$payload = $t[1];

			if ($payload) {
				$payload = json_decode(base64_decode($payload));

				return $payload;
			}
		}
		return false;
	}

	public static function getUser () {
		$payload = self::getJWTPayload();

		return $payload->login;
	}

	public static function JWTStructureVerify () {
		if ($token = self::getToken()) {
			// Explode in token
			$t = explode('.', $token);
			$header = $t[0];
			$payload = $t[1];
			$assignature = $t[2];

			if ($assignature && $header && $payload) {
				// Generate secret toke to compare
				$token = hash_hmac('sha256', $header . '.' . $payload, \Config::get('app.key'), true);
				$token = base64_encode($token);
				if ($token == $assignature) {
					return true;
				}
			}
		}
		return false;
	}

	public static function JWTExpired () {
		if ($p = self::getJWTPayload()) {
			$exp = date('Y-m-d H:i:s', $p->exp);
			$Exp = new \DateTime($exp);
			$Now = new \DateTime();

			if ($Exp > $Now) {
				return false;
			}
		}
		return \Response::json([
			'response' => [
				'status' => 306,
				'error' => [
					'message' => 'Token expired!!'
				]
			]
		], 401);
	}
}
