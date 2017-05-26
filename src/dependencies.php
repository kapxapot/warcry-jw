<?php

$container['logger'] = function($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new \Monolog\Logger($settings['name']);
    $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
    $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], \Monolog\Logger::DEBUG));
    
    return $logger;
};

$container['auth'] = function($c) {
	$auth = new \App\Auth\Auth($c);

	return $auth;
};

$container['flash'] = function($c) {
	return new \Slim\Flash\Messages;
};

$container['view'] = function($c) {
    $settings = $c->get('settings')['twig'];
	
	$loader = new \Twig_Loader_Filesystem($settings['templates_path']);
	$view = new \Twig_Environment($loader, array(
	    'cache' => $settings['cache_path'],
	));
	
	$view->addExtension(new \Slim\Views\TwigExtension($c->router, $c->request->getUri()));

	$view->addGlobal('auth', [
		'check' => $c->auth->check(),
		'user' => $c->auth->user()
	]);
	
	$view->addGlobal('flash', $c->flash);
	
    return $view;
};

$container['cache'] = function($c) {
	return new \Warcry\Cache($c);
};

$container['validator'] = function($c) {
	return new \App\Validation\Validator($c);
};

$container['AuthController'] = function($c) {
	return new \App\Controllers\Auth\AuthController($c);
};

$container['PasswordController'] = function($c) {
	return new \App\Controllers\Auth\PasswordController($c);
};

$container['csrf'] = function($c) {
	return new \Slim\Csrf\Guard;
};

$container['db'] = function($c) {
	$dbs = $c->get('settings')['db'];
	
	ORM::configure("mysql:host={$dbs['host']};dbname={$dbs['database']}");
	ORM::configure("username", $dbs['user']);
	ORM::configure("password", $dbs['password']);
	ORM::configure("driver_options", array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
	
	return new \Warcry\ORM\Idiorm\DbHelper($c);
};

$container['notFoundHandler'] = function($c) {
	return function($request, $response) use ($c) {
		$render = $c->view->render('errors/404.twig');
		$response->write($render);
		
		return $response->withStatus(404);
	};
};
