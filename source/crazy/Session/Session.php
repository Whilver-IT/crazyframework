<?php

namespace Crazy\Session;

/**
 * セッション処理
 */
class Session {

	/**
	 * session_regenerate_idを呼び出す間隔
	 * php.iniのsession.gc_maxlifetimeの時間を確認して適切な値をセットすること
	 */
	const GENERATE_TIMEDIFF = 300;

	/**
	 * コンストラクタ
	 */
	private function __construct(){}

	/**
	 * セッションの開始(共通で受ける場所)
	 *
	 * @return void
	 */
	public static function start(bool $is_regenerate = false): void {
		$now = time();
		session_start();
		if(is_nullorempty($_SESSION['difftime'])){
			$_SESSION['difftime'] = $now;
		}
		// セッションIDの再作成はGENERATE_TIMEDIFF秒おきにする
		if($is_regenerate || $now >= $_SESSION['difftime'] + self::GENERATE_TIMEDIFF){
			session_regenerate_id(true);
			$_SESSION['difftime'] = $now;
		}
	}

	/**
	 * セッションの破棄
	 *
	 * @return void
	 */
	public static function destroy(): void {
		session_destroy();
	}
}