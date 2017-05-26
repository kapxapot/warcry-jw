<?php

namespace App\Controllers\Auth;

use Warcry\Slim\Controllers\Controller;
use Respect\Validation\Validator as v;

class PasswordController extends Controller {
	public function getChangePassword($request, $response) {
		$render = $this->view->render('auth/password/change.twig');
		$response->write($render);
	}

	public function postChangePassword($request, $response) {
		$user = $this->auth->user();

		$validation = $this->validator->validate($request, [
			'password_old' => v::noWhitespace()->notEmpty()->matchesPassword($user->password),
			'password' => v::noWhitespace()->notEmpty()
		]);
		
		if ($validation->failed()) {
			return $response->withRedirect($this->router->pathFor('auth.password.change'));
		}
		
		$user->set('password', password_hash($request->getParam('password'), PASSWORD_DEFAULT));
		$user->save();
		
		$this->logger->info("Changed password for user: {$this->auth->userString()}");
		
		$this->flash->addMessage('info', 'Пароль успешно изменен.');
		
		return $response->withRedirect($this->router->pathFor('home'));
	}
}
