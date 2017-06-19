<?php

namespace App\Http\Controllers\Website;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ContactController extends Controller {
	private function validation($r, \Closure $success, \Closure $error) {
		$m = [];

		if(empty($r->name)) {
			$m['name'] = 'É necessário informar seu nome.';
		} else {
			if(strlen($r->name) <= 3) {
				$m['name'] = 'O nome informado é inválido.';
			}
		}

		if(empty($r->email)) {
			$m['email'] = 'É necessário informar seu endereço de e-mail.';
		} else {
			if(!filter_var($r->email, FILTER_VALIDATE_EMAIL)) {
				$m['email'] = 'O e-mail informado é inválido.';
			}
		}

		if(empty($r->message)) {
			$m['message'] = 'É necessário escrever a mensagem que deseja enviar.';
		} else {
			if(strlen($r->message) < 10) {
				$m['message'] = 'A mensagem deve ter no mínimo 10 caracteres.';
			}
		}

		if(count($m) == 0) {
			return $success();
		} else {
			return $error($m);
		}
	}

    public function sendContact(Request $request) {
    	return $this->validation($request, function() use ($request) {
    		try {
			    \Mail::send('mail.contact', [
				    'name' => $request->name,
				    'email' => $request->email,
				    'body' => $request->message
			    ], function ($m) use ($request) {
				    $m->from(env('MAIL_USERNAME'), 'Contato pelo Website.');
				    $m->replyTo($request->email);
				    $m->to(env('CONTACT_MAIL'), $request->name)->subject("Contato através do Website");
			    });

			    return \Response::json([
			    	'success' => [
			    		'message' => 'Formulário de contato enviado com sucesso.'
				    ]
			    ], 200);
		    } catch (\Exception $e) {
    			return \Response::json([
    				'error' => [
    					'message' => 'Erro interno ao enviar o e-mail.',
					    'internal' => [
					    	'message' => $e->getMessage(),
						    'line' => $e->getLine(),
						    'file' => $e->getFile()
					    ]
				    ]
			    ], 500);
		    }
	    }, function($m) {
		    return \Response::json([
		    	'validation' => $m
		    ], 400);
	    });
    }
}
