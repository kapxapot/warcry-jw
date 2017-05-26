<?php

namespace App\Middleware;

use Warcry\Slim\Middleware\Middleware;

class GuestMiddleware extends Middleware {
	public function __invoke($request, $response, $next) {
		if ($this->auth->check()) {
			return $response->withRedirect($this->router->pathFor('home'));
		}

		$response = $next($request, $response);
		
		return $response;
	}
}
