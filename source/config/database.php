<?php

use \Crazy\Config\Environment as Environment;

/**
 * データベース設定をここに記述.envで指定された値をここで読み込む
 */
return [
	'default' => Environment::get("DB_CONNECTION_01", "mysql"),
	'connections' => [

		'main' => [
			'type' => Environment::get("DB_TYPE_01"),
			'host' => Environment::get("DB_HOST_01"),
			'port' => Environment::get("DB_PORT_01"),
			'dbname' => Environment::get("DB_DATABASE_01"),
			'user' => Environment::get("DB_USERNAME_01"),
			'passwd' => Environment::get("DB_PASSWORD_01"),
		],

		'pgsql' => [
			'type' => 'pgsql',
			'host' => Environment::get("DB_HOST", "127.0.0.1"),
			'port' => Environment::get("DB_PORT", 5432),
			'dbname' => Environment::get("DB_DATABASE", "pg"),
			'user' => Environment::get("DB_USERNAME", "pg"),
			'passwd' => Environment::get("DB_PASSWORD", ""),
		],

		'mysql' => [
			'type' => 'mysql',
			'host' => Environment::get("DB_HOST", "127.0.0.1"),
			'port' => Environment::get("DB_PORT", 3306),
			'dbname' => Environment::get("DB_DATABASE", "mysql"),
			'user' => Environment::get("DB_USERNAME", "mysql"),
			'passwd' => Environment::get("DB_PASSWORD", ""),
		],

	]
];
