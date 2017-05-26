<?php

namespace App\Middleware;

use Warcry\Slim\Middleware\Middleware;

class CsrfViewMiddleware extends Middleware {
	public function __invoke($request, $response, $next) {
		$csrf = $this->csrf;
		
		if ($csrf->getTokenName() == null) {
			$csrf->generateToken();
		}
		
		$this->view->addGlobal('csrf', [
			'field' => '
				<input type="hidden" name="'.$csrf->getTokenNameKey().'" value="'.$csrf->getTokenName().'" />
				<input type="hidden" name="'.$csrf->getTokenValueKey().'" value="'.$csrf->getTokenValue().'" />
			'
		]);

		$response = $next($request, $response);
		
		return $response;
	}
}
