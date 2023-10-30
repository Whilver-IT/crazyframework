<?php
/**
 * ルーティングの記述をここで行う
 */

use \App\Http\Controllers\LoginController;
use \App\Http\Controllers\MenuController;
use \App\Http\Controllers\CreateUserController;
use \App\Http\Controllers\SearchZipController;

return [
	[
		'prefix' => '',
		'info' => [
			["top", "/", "get", null, [], ],
			["menu", "/menu", "get", null, [MenuController::class, "index"], ],
			["logout", "/logout", "get", null, [], ],
			['call', '/a/:id/b', 'get', null, function($param) { return "<pre>" . print_r($param, true) . "</pre>"; }, ],
			['createuser', '/createuser', 'get', null, [CreateUserController::class, "index"], false, ],
			['createuser.finish', '/createuser/finish', 'get', null, [CreateUserController::class, "finish"], false, ],
		],
	],
	[
		'prefix' => 'ajax',
		'info' => [
			['login', '/login', 'patch', null, [LoginController::class, 'index'],],
			['createuser', '/createuser', 'patch', null, [CreateUserController::class, 'create'], false],
			['searchzip', '/searchzip', 'get', null, [SearchZipController::class, 'index'], false],
		]
	],
];