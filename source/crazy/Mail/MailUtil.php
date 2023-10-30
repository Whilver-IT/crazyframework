<?php

namespace Crazy\Mail;

use \Crazy\Validator\Validator;

/**
 * メールユーティリティクラス
 */
class MailUtil {

	/**
	 * コンストラクタ
	 */
	private function __construct(){}

	/**
	 * メールアドレスチェック
	 * TODO まだ
	 *
	 * @param string $mailAddress
	 * @param boolean $isCheckMX
	 * @return boolean
	 */
	public static function isCorrectMailAddress(string $mailAddress, bool $isCheckMX = false): bool {

		return true;
	}
}