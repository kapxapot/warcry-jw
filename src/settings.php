<?php

return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
        ],

        'db' => [
			'host' => 'host',
			'database' => 'database',
			'user' => 'user',
			'password' => 'password',
        ],

        'tables' => [
        	'heroes' => [
        		'table' => 'heroes',
        		'fields' => ['id', 'name'],
        		'sort' => 'name',
        	],
        	'arena_counter_picks' => [
        		'table' => 'arena_counter_picks',
        		'fields' => ['id', 'pick', 'counter_pick', 'pick_ids', 'counter_pick_ids', 'created_at'],
        		'sort' => 'created_at',
        		'reverse' => true,
        	],
        	'users' => [
        		'table' => 'users',
        	]
       	],
       	
       	'twig' => [
       		'templates_path' => __DIR__ . '/../templates/',
       		'cache_path' => false,//__DIR__ . '/../cache/'
       	],
       	
       	'auth' => [
       		'jw_password' => 'password',
       	],
       	
       	'baseline' => '2016-12-01',
    ],
];
