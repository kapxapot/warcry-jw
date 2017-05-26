<?php

namespace App\Middleware;

use Warcry\Slim\Middleware\Middleware;

class AuthMiddleware extends Middleware {
	public function __invoke($request, $response, $next) {
		if (!$this->auth->check()) {
			$this->flash->addMessage('error', 'Пожалуйста, сначала войдите на сайт.');
			return $response->withRedirect($this->router->pathFor('auth.signin'));
		}

		$response = $next($request, $response);
		
		return $response;
	}
}
