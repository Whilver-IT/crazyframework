<?php

namespace App\Http\Controllers;

use \Crazy\Html\HtmlUtil;
use \App\Http\Controllers\ControllerBase;

/**
 * ログイン画面Ajaxコントローラクラス
 * 
 * ログイン画面から呼ばれるajaxの処理をまとめたクラス
 */
class LoginController extends ControllerBase {

    /**
     * index
     * 
     * ログインボタン押下時の処理を記述
     * 実際にはbootstrap/app.phpのsendメソッド内でログインの処理を行い
     * その結果等をjson文字列で返す
     *
     * @return string
     */
    public function index(): string {
        $result = [
            'exec_login' => !is_nullorempty($_POST['userid'] ?? null) && !is_nullorempty($_POST['password'] ?? null),
            'is_login' => is_nullorempty($_SESSION['user']['id'] ?? null) ? false : true,
            '_csrf' => HtmlUtil::csrf(true),
        ];
        return json_encode($result);
    }
}