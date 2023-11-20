<?php

namespace App\Services;

use \App\Services\ServiceBase;
use \App\Services\UserService;

/**
 * 認証関連のサービスクラス
 */
class AuthService extends ServiceBase {

    /**
     * ユーザIDとパスワードによるチェック
     *
     * @return boolean
     */
    public static function check(): bool {

        $user_info = ['id' => ""];
        if(is_nullorempty($_SESSION['user']['id'])){
            $user_info = [
                'id' => is_nullorempty($_POST['userid']) ? "" : $_POST['userid'],
                'password' => is_nullorempty($_POST['password']) ? "" : $_POST['password'],
            ];
        } else {
            $user_info['id'] = $_SESSION['user']['id'];
        }
        unset($_SESSION['user']);
        $login_info = (new UserService())->getUser($user_info);
        if(count($login_info)){
            $_SESSION['user'] = $login_info[0];
        }

        return is_nullorempty($_SESSION['user']['id']) ? false : true;
    }
}
