<?php

namespace App\Http\Controllers;

use \App\Services\UserService;
use \Crazy\Validator\Validator;
use \Crazy\View\View;

/**
 * ユーザ作成画面コントローラクラス
 * 
 */
class CreateUserController extends ControllerBase {

	/**
	 * バリデーションルール
	 */
	const VALIDATE_LIST = [
		'id' => [
			'required' => [
				'err_msg' => "IDが入力されていません",
			],
			'enterable' => [
				'err_msg' => "通常は表示できない特殊文字が含まれています",
			],
			'max' => [
				'length' => 32,
				'err_msg' => "IDは半角英数32文字以内で入力してください",
			],
		],
		'password' => [
			'required' => [
				'err_msg' => "パスワードが入力されていません",
			],
			'enterable' => [
				'err_msg' => "通常では表示できない特殊文字が含まれています",
			],
			'min' => [
				'length' => 8,
				'err_msg' => "パスワードは8文字以上64文字以下で入力してください",
			],
			'max' => [
				'length' => 64,
				'err_msg' => "パスワードは8文字以上64文字以下で入力してください",
			],
			'confirmed' => [
				'err_msg' => "パスワードとパスワード(確認)が一致していません",
			],
		],
		'fname' => [
			'required' => [
				'err_msg' => "氏名(姓)が入力されていません",
			],
		],
		'name' => [
			'required' => [
				'err_msg' => "氏名(名)が入力されていません",
			],
		],
		'byear' => [
			'nullable' => [],
			'required_unless' => [
				'items' => [
					'bmonth',
					'bday',
				],
				'err_msg' => "誕生日(月)または誕生日(日)の入力がある場合は、誕生日(年)を入力してください",
			],
		],
		'bmonth' => [
			'nullable' => [],
			'required_unless' => [
				'items' => [
					'byear',
					'bday',
				],
				'err_msg' => "誕生日(年)または誕生日(日)の入力がある場合は、誕生日(月)を入力してください",
			],
		],
		'bday' => [
			'nullable' => [],
			'required_unless' => [
				'items' => [
					'byear',
					'bmonth',
				],
				'err_msg' => "誕生日(年)または誕生日(月)の入力がある場合は、誕生日(日)を入力してください",
			],
		],
		'tel1' => [
			'nullable' => [],
			'tel' => [
				'err_msg' => "電話番号の形式ではありません",
			],
		],
		'tel2' => [
			'nullable' => [],
			'tel' => [
				'err_msg' => "電話番号の形式ではありません",
			],
		],
		'mail1' => [
			'nullable' => [],
			'email' => [
				'err_msg' => "emailの形式でないか、ドメインが存在しない可能性があります"
			],
		],
		'mail2' => [
			'nullable' => [],
			'email' => [
				'err_msg' => "emailの形式でないか、ドメインが存在しない可能性があります"
			],
		],
	];

	/**
	 * 入力画面表示
	 *
	 * @return string
	 */
	public function index(): string {
		return View::make("createuser.index", []);
	}

	/**
	 * ユーザ情報作成
	 *
	 * @return string
	 */
	public function create(): string {

		unset($_SESSION['create_user']);

		$validator = Validator::getValidate(self::VALIDATE_LIST);
		$validate_result = Validator::validate($_POST, $validator);
		$validator_date = [];
		if(!(array_key_exists('byear', $validate_result)
		|| array_key_exists('bmonth', $validate_result)
		|| array_key_exists('bday', $validate_result))){
			$value['birth'] = $_POST['byear'] . "-" . $_POST['bmonth'] . "-" . $_POST['bday'];
			if(!is_nullorempty(str_replace("-", "", $value['birth']))){
				$validator_date = [
					'birth' => [
						'date' => [
							'err_msg' => "誕生日が正しい日付ではありません",
						],
					],
				];
				$validate_result += Validator::validate($value, Validator::getValidate($validator_date));
			}
		}

		$err_msg = [];
		if(!is_nullorempty($validate_result)){
			$validator_list = is_nullorempty($validator_date) ? self::VALIDATE_LIST : self::VALIDATE_LIST + $validator_date;
			$err_msg = Validator::getErrMsg($validator_list, $validate_result);
		}

		$result['err_msg'] = [];
		if(is_nullorempty($err_msg)){
			$userService = new UserService();
			$result = $userService->createUser($_POST);
			if(is_nullorempty($result['err_msg'])){
				$_SESSION['create_user'] = true;
			}
			$result['session'] = $_SESSION;
		} else {
			$result['err_msg'] = $err_msg;
		}

		return json_encode($result);
	}

	/**
	 * ユーザ登録完了画面
	 *
	 * @return string
	 */
	public function finish(): string {
		if(is_nullorempty($_SESSION['create_user']) || $_SESSION['create_user'] !== true){
			header("HTTP/1.1 404 Not Found");
			exit;
		}
		unset($_SESSION['create_user']);
		return View::make("createuser.finish", []);
	}
}