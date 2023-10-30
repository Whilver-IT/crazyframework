<?php

namespace App\Services;

use \PDO;
use \Crazy\Database\DB;

class ServiceBase {

	/**
	 * DB情報
	 *
	 * @var PDO
	 */
	protected PDO $db;

	/**
	 * コンストラクタ
	 *
	 * @param string $connName
	 */
	public function __construct(string $connName = ""){
		if(!is_nullorempty($connName)){
			DB::setConnection($connName);
		}
		$this->db = DB::getConnection();
	}
}