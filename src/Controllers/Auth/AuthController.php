<?php

namespace App\Controllers\Auth;

use Warcry\Slim\Controllers\Controller;
use Respect\Validation\Validator as v;

class AuthController extends Controller {
	public function getSignUp($request, $response) {
		$render = $this->view->render('auth/signup.twig');
		$response->write($render);
	}
	
	public function postSignUp($request, $response) {
		$validation = $this->validator->validate($request, [
			'email' => v::noWhitespace()->notEmpty()->email()->emailAvailable(),
			'name' => v::notEmpty()->alpha(),
			'password' => v::noWhitespace()->notEmpty()
		]);
		
		if ($validation->failed()) {
			return $response->withRedirect($this->router->pathFor('auth.signup'));
		}
		
		$user = $this->db->forTable('users')->create();

		$user->set('email', $request->getParam('email'));
		$user->set('name', $request->getParam('name'));
		$user->set('password', password_hash($request->getParam('password'), PASSWORD_DEFAULT));
		$user->set('updated_at', date("Y-m-d H:i:s"));
		$user->save();
		
		// message
		$this->flash->addMessage('info', 'Вы успешно зарегистрировались!');
		
		// signing in
		$this->auth->attempt($user->email, $request->getParam('password'));
		
		$this->logger->info("Created user: {$this->auth->userString()}");
		
		return $response->withRedirect($this->router->pathFor('home'));
	}

	public function getSignIn($request, $response) {
		$render = $this->view->render('auth/signin.twig');
		$response->write($render);
	}
	
	public function postSignIn($request, $response) {
		$auth = $this->auth->attempt(
			$request->getParam('email'),
			$request->getParam('password')
		);
		
		if (!$auth) {
			$this->flash->addMessage('error', 'Пользователь с таким логином и паролем не найден.');
			
			return $response->withRedirect($this->router->pathFor('auth.signin'));
		}
		
		$this->logger->info("User logged in: {$this->auth->userString()}");
		
		return $response->withRedirect($this->router->pathFor('home'));
	}
	
	public function getSignOut($request, $response) {
		$this->auth->logout();
		
		return $response->withRedirect($this->router->pathFor('home'));
	}
}
