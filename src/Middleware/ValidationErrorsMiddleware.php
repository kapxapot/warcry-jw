<?php

namespace App\Middleware;

use Warcry\Slim\Middleware\Middleware;

class ValidationErrorsMiddleware extends Middleware {
	public function __invoke($request, $response, $next) {
		$this->view->addGlobal('errors', $_SESSION['errors']);
		unset($_SESSION['errors']);
		
		$response = $next($request, $response);
		
		return $response;
	}
}
