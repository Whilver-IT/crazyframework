<?php

namespace App\Http\Controllers;

use \App\Http\Controllers\ControllerBase;
use \App\Services\AddressService;

/**
 * 郵便番号検索Ajax
 * 
 * 郵便番号検索で呼ばれるAjaxの処理をまとめたクラス
 */
class SearchZipController extends ControllerBase {

	public function index() {
		$zip_info = [];
		if(!is_nullorempty($_GET['zip'] ?? null)){
			$zip_info = (new AddressService)->getZipInfoAll($_GET['zip']);
		}
		return json_encode($zip_info);
	}
}