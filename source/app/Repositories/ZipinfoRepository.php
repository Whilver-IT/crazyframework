<?php

namespace App\Repositoies;

use \Crazy\Database\DB;
use \App\Repositoies\RepositoryBase;
use \PDO;

/**
 * 郵便番号情報取得DBクラス
 */
class ZipinfoRepository extends RepositoryBase {

	/**
	 * 全国郵便番号情報取得SQL
	 *
	 * @param array $param
	 * @return array
	 */
	public function getZipInfo(array $param): array {
		$sql_param = [
			'select' => [
				"id",
				"zip",
				"pref_cd",
				"city_cd",
				"city",
				"city_kana",
				"street",
				"street_kana",
				"zipinfo.created_user as zipinfo-created_user",
				"zipinfo.created_at as zipinfo-created_at",
				"zipinfo.updated_user as zipinfo-updated_user",
				"zipinfo.updated_at as zipinfo-updated_at",
				"cd",
				"name",
				"suffix",
				"prefectures.created_user as prefectures-created_user",
				"prefectures.created_at as prefectures-created_at",
				"prefectures.updated_user as prefectures-updated_user",
				"prefectures.updated_at as prefectures-updated_at",
			],
			'from' => [
				"zipinfo",
				"prefectures",
			],
		];

		foreach(self::SQL_FACTOR as $factor){
			if(!is_nullorempty($param[$factor])){
				$sql_param[$factor] = $param[$factor];
			}
		}

		$sql = $this->simpleSelect($sql_param);
		$stmt = $this->db->prepare($sql);
		if(!is_nullorempty($zip)){
			$stmt->bindValue(":zip", $zip);
		}
		$stmt->execute();
		$zip_info = [];
		while(($row = $stmt->fetch(PDO::FETCH_ASSOC)) != false){
			$zip_info[] = $row;
		}
		$stmt->closeCursor();

		return $zip_info;
	}

	/**
	 * 事業所用郵便番号情報取得SQL
	 *
	 * @param array $param
	 * @return array
	 */
	public function getOfiicesZipInfo(array $param): array {
		$sql_param = [
			'select' => [
				"id",
				"zip",
				"pref_cd",
				"city_cd",
				"city",
				"street",
				"other",
				"company_name",
				"dompany_kana",
				"offices_zipinfo.created_user as offices_zipinfo-created_user",
				"offices_zipinfo.created_at as offices_zipinfo-created_at",
				"offices_zipinfo.updated_user as offices_zipinfo-updated_user",
				"offices_zipinfo.updated_at as offices_zipinfo-updated_at",
				"cd",
				"name",
				"suffix",
				"prefectures.created_user as prefectures-created_user",
				"prefectures.created_at as prefectures-created_at",
				"prefectures.updated_user as prefectures-updated_user",
				"prefectures.updated_at as prefectures-updated_at",
			],
			'from' => [
				"offices_zipinfo",
				"prefectures",
			],
		];

		foreach(self::SQL_FACTOR as $factor){
			if(!is_nullorempty($param[$factor])){
				$sql_param[$factor] = $param[$factor];
			}
		}

		$sql = $this->simpleSelect($sql_param);
		$stmt = $this->db->prepare($sql);
		foreach($param['where'] as $key => $value){
			$stmt->bindValue(":" . $key, $value, PDO::PARAM_STR);
		}
		$stmt->execute();
		$zip_info = [];
		while(($row = $stmt->fetch(PDO::FETCH_ASSOC)) != false){
			$zip_info[] = $row;
		}
		$stmt->closeCursor();

		return $zip_info;
	}

	/**
	 * 全国、事業所郵便番号情報取得用SQL作成
	 *
	 * @param array $param
	 * @return array
	 */
	public function getZipInfoAll(array $param): array {
		$sql_param = [
			'select' => [
				"zip",
				"pref_cd",
				"city_cd",
				"city",
				"city_kana",
				"street",
				"street_kana",
				"null as other",
				"null as company_name",
				"null as company_kana",
				"cd",
				"name",
				"suffix",
			],
			'from' => [
				"zipinfo",
				"prefectures"
			],
		];

		foreach(self::SQL_FACTOR as $factor){
			if(!is_nullorempty($param[$factor])){
				$sql_param[$factor] = $param[$factor];
			} elseif($factor == "whereColumn"){
				$sql_param[$factor][] = ["pref_cd", "cd"];
			}
		}

		$sql_zip_info = str_replace(":zip", ":zip1", $this->simpleSelect($sql_param));

		$sql_param['select'] = [
			"zip",
			"pref_cd",
			"city_cd",
			"city",
			"null as city_kana",
			"street",
			"null as street_kana",
			"other",
			"company_name",
			"company_kana",
			"cd",
			"name",
			"suffix",
		];
		$sql_param['from'] = [
			"offices_zipinfo",
			"prefectures",
		];
		
		$sql_offices_zipinfo = str_replace(":zip", ":zip2", $this->simpleSelect($sql_param));

		$sql = $sql_zip_info . " union all " . $sql_offices_zipinfo;
		$stmt = $this->db->prepare($sql);
		if(!is_nullorempty($param['where']['zip'])){
			$stmt->bindValue(":zip1", $param['where']['zip'], PDO::PARAM_STR);
			$stmt->bindValue(":zip2", $param['where']['zip'], PDO::PARAM_STR);
		}
		$stmt->execute();
		$zip_info = [];
		while(($row = $stmt->fetch(PDO::FETCH_ASSOC)) != false){
			$zip_info[] = $row;
		}
		$stmt->closeCursor();

		return $zip_info;
	}
}