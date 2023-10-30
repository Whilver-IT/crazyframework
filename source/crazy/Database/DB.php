<?php

namespace Crazy\Database;

use \Bootstrap\App;
use \PDO;
use \Throwable;

/**
 * データベースクラス
 */
class DB {

	/**
	 * 現在の接続名
	 *
	 * @var string
	 */
	private static string $connName = "";

	/**
	 * DBの設定情報(BASE_DIR/config/database.php)を格納
	 *
	 * @var array
	 */
	private static array $settings = [];

	/**
	 * データベースオブジェクト(複数持てるように配列で持つ)
	 *
	 * @var array
	 */
	private static array $dbObj = [];

	/**
	 * コンストラクタ
	 * インスタンスは生成させないので、privateで
	 */
	private function __construct(){}

	/**
	 * コネクションの設定を変更
	 *
	 * @param string $connName
	 * @return void
	 */
	public static function setConnection(string $connName): void {
		if(!is_nullorempty($connName)){
			self::$connName = $connName;
		}
	}

	/**
	 * コネクションを取得
	 *
	 * @return PDO
	 */
	public static function getConnection() : PDO {

		if(is_nullorempty(self::$settings)){
			self::$settings = include(implode(DIRECTORY_SEPARATOR, [App::$BASE_DIR, "config", "database.php"]));
		}

		if(is_nullorempty(self::$connName)){
			self::$connName = self::$settings['default'];
		}

		$dbObj = null;
		if(array_key_exists(self::$connName, self::$dbObj)){
			$dbObj = self::$dbObj[self::$connName];
		} else {
			$connInfo = self::$settings['connections'][self::$connName];
			$options = [
				PDO::ATTR_CASE => PDO::CASE_LOWER,
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			];
			try {
				$dbObj = new PDO(self::getDsn($connInfo), $connInfo['user'], $connInfo['passwd'], $options);
			} catch(Throwable $th){
				header("HTTP/1.1 500 Internal Server Error");
				exit();
			}
			self::$dbObj[self::$connName] = $dbObj;
		}

		return $dbObj;
	}

	/**
	 * DSNを取得
	 *
	 * @param array $connInfo
	 * @return string
	 */
	private static function getDsn(array $connInfo) : string {
		$dsn = "";
		switch($connInfo['type']){
		case "mysql":
		case "pgsql":
			$port = is_nullorempty($connInfo['port']) ? ($connInfo['type'] == "mysql" ? "3306" : "5432") : $connInfo['port'];
			$dsn = $connInfo['type'] . ":dbname=" . $connInfo['dbname'] . ";host=" . $connInfo['host'] . ";port=" . $port;
			break;
		case "mssql":
			$dsn = "sqlsrv:" . "Server=" . $connInfo['host'] . "," . (is_nullorempty($connInfo['port']) ? "1433" : $connInfo['port']) . ";Database=" . $connInfo['dbname'] . ";TrustServerCertificate=1";
			break;
		case "oracle":
			$dsn = "oci:dbname=";
        	if(isset($connInfo['host']) && strlen($connInfo['host'])){
            	$dsn .= "//" . $connInfo['host'] . ":" . (is_nullorempty($connInfo['port']) ? "1521" : $connInfo['port']) . "/" . $connInfo['dbname'];
        	} else {
            	$dsn .= $connInfo['dbname'];
        	}
			$dsn .= ";charset=AL32UTF8";
			break;
		default:
			break;
		}

		return $dsn;
	}
}
