<?php

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;

use Warcry\Slim\Middleware\JsonResponseMiddleware;

use Warcry\Exceptions\BadRequestException;
use Warcry\Exceptions\AuthenticationException;
use Warcry\Exceptions\ApplicationException;

require_once __DIR__ . '/routeFunctions.php';

$app->group('/jw', function() {
	$container = $this->getContainer();
	
	$this->group('/api', function() {
	    $this->group('/v1', function() {
			$this->get('/heroes', function($request, $response, $args) {
				return $this->db->jsonMany($response, 'heroes');
			});
			
			$this->get('/heroes/{id:\d+}', function($request, $response, $args) {
				$hero = getHeroById($args['id']);
				return $this->db->jsonOne($response, $hero);
			});

		    $this->group('/arena', function() {
				$this->get('/counter-picks', function($request, $response, $args) {
					$picks = $this->db->getMany('arena_counter_picks');

					$prepPicks = [];
					foreach ($picks as $pick) {
						$prepPicks[] = prepareCounterPick($this, $pick);
					}
						
					return $this->db->json($response, $prepPicks);
				});

				$this->get('/counter-picks/{ids}', function($request, $response, $args) {
					try {
						$ids = stringToIds($args['ids']);
						if (count($ids) != 5) {
							throw new BadRequestException('В команде должно быть 5 героев.');
						}
						
						$requestStr = idsToString($ids);
	
						$pickPairs = $this->db->forTable('arena_counter_picks')
							->where_gte('created_at', $this->get('settings')['baseline'])
							->where('pick_ids', $requestStr)
							->findMany();

						$counterPicks = [];
						
						foreach ($pickPairs as $pair) {
							$counterPicks[] = prepareCounterPick($this, $pair);
						}
	
						return $this->db->json($response, $counterPicks);
					}
					catch (Exception $ex) {
						$this->db->error($response, $ex);
					}
				});

				$this->post('/counter-pick', function($request, $response, $args) {
					try {
						$data = $request->getParsedBody();
						
						$pass = $data['password'];
						if ($pass != $this->get('settings')['auth']['jw_password']) {
							throw new AuthenticationException('Неверный пароль.');
						}
							
						$enemyHeroes = $data['enemy_heroes'];
						$myHeroes = $data['my_heroes'];
						
						if (count($enemyHeroes) != 5) {
							throw new BadRequestException('В команде противника должно быть 5 героев.');
						}
						
						if (count($myHeroes) != 5) {
							throw new BadRequestException('В вашей команде должно быть 5 героев.');
						}
						
						$pickIds = idsToString($enemyHeroes);
						$counterPickIds = idsToString($myHeroes);
						
						$count = $this->db->forTable('arena_counter_picks')
							->where_gte('created_at', $this->get('settings')['baseline'])
							->where('pick_ids', $pickIds)
							->where('counter_pick_ids', $counterPickIds)
							->count();
							
						if ($count > 0) {
							throw new ApplicationException('Такая запись уже существует.');
						}
						
						$e = $this->db->forTable('arena_counter_picks')->create();

						$e->set('pick_ids', $pickIds);
						$e->set('counter_pick_ids', $counterPickIds);
						$e->set('pick', idsToHeroesString($this, $enemyHeroes));
						$e->set('counter_pick', idsToHeroesString($this, $myHeroes));
						$e->save();
						
						$this->logger->info("Created arena counter pick: {$e->id}");
						
						$response = $response->withStatus(201);
				
						return $this->db->jsonOne($response, prepareCounterPick($this, $e));
					}
					catch (Exception $ex) {
						$this->db->error($response, $ex);
					}
				});
		    });
	    });
	})->add(new JsonResponseMiddleware($container));

	$this->group('/secret', function() {
		$this->get('/arena', function($request, $response, $args) {
			$render = $this->view->render('counter-picks/search.twig');
			$response->write($render);
	    })->setName('home');
		
		$this->get('/arena/add', function($request, $response, $args) {
			$render = $this->view->render('counter-picks/add.twig');
			$response->write($render);
	    })->setName('counter-picks.add');
		
		$this->get('/arena/all', function($request, $response, $args) {
			$render = $this->view->render('counter-picks/all.twig', [
				'sort' => 'created_at',
				'reverse' => true,
				'columns' => [
					'id' => 'id',
					'pick_string' => 'Отряд противника',
					'counter_pick_string' => 'Рекомендуемый отряд',
					'created_at' => 'Дата добавления'
				]
			]);
			$response->write($render);
	    })->setName('counter-picks.all');
	
		$this->get('/arena/stats', function($request, $response, $args) {
			$render = $this->view->render('dev/counter-picks-stats.twig');
			$response->write($render);
	    });
	
		$this->get('/arena/fix', function($request, $response, $args) {
			try {
				$heroes = $this->db->forTable('heroes')
					->findArray();
					
				$heroesByName = [];
				foreach ($heroes as $hero) {
					$heroesByName[$hero['name_en']] = $hero;
				}
	
				$pickPairs = $this->db->forTable('arena_counter_picks')
					->where('pick_ids', '')
					->findMany();
	
				$result = [];
				
				foreach ($pickPairs as $pair) {
					$pair->pick_ids = idsToString(heroesStringToIds($this, $heroesByName, $pair->pick));
					$pair->counter_pick_ids = idsToString(heroesStringToIds($this, $heroesByName, $pair->counter_pick));
	
					$pair->save();
					
					$result[] = $pair->as_array();
				}
	
				return $this->db->json($response, $result);
			}
			catch (Exception $ex) {
				$this->db->error($response, $ex);
			}
		});
	});

	$this->group('/auth', function() {
		$this->get('/signup', 'AuthController:getSignUp')->setName('auth.signup');
		$this->post('/signup', 'AuthController:postSignUp');

		$this->get('/signin', 'AuthController:getSignIn')->setName('auth.signin');
		$this->post('/signin', 'AuthController:postSignIn');
	})->add(new GuestMiddleware($container))->add($container->csrf);
	
	$this->group('/auth', function() {
		$this->get('/signout', 'AuthController:getSignOut')->setName('auth.signout');

		$this->get('/password/change', 'PasswordController:getChangePassword')->setName('auth.password.change');
		$this->post('/password/change', 'PasswordController:postChangePassword');
	})->add(new AuthMiddleware($container))->add($container->csrf);
});
