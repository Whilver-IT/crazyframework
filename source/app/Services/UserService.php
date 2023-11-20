<?php

namespace App\Services;

use \App\Services\ServiceBase;
use \App\Repositoies\UsermasterRepository;
use \Exception;
use \Throwable;

class UserService extends ServiceBase {

    /**
     * ユーザ取得
     *
     * @param string $id
     * @return array
     */
    public function getUser(array $user_info = []): array {
        $is_need_passwd = isset($user_info['password']);
        $password = (is_nullorempty($user_info['password'])) ? "" : $user_info['password'];
        if($is_need_passwd){
            unset($user_info['password']);
        }
        try {
            return (new UsermasterRepository($this->db))->getUser(is_nullorempty($user_info) ? [] : ['where' => $user_info]);
        } catch(Throwable $th){
            throw $th;
        }
    }

    /**
     * ユーザ作成
     *
     * @param array $user_info
     * @return array
     */
    public function createUser(array $user_info): array {

        $is_transaction = false;
        $result = ['err_msg' => [],];
        $stmt = null;
        try {
            $this->db->beginTransaction();
            $is_transaction = true;

            if(is_nullorempty($user_info['id'])){
                $result['err_msg']['id'] = "ユーザ情報にIDが指定されていません";
                throw new Exception($result['err_msg']['id']);
            }

            $user = $this->getUser(['id' => $user_info['id']]);
            if(!is_nullorempty($user)){
                $result['err_msg']['id'] = "ユーザが既に指定されています";
                throw new Exception($result['err_msg']['id']);
            }
            (new UsermasterRepository($this->db))->createUser($user_info);
            $this->db->commit();
        }catch(Throwable $th){
            if($is_transaction){
                if(!is_null($stmt)){
                    $stmt->closeCursor();
                }
                $this->db->rollBack();
                $result['err_msg']['db'] = $th->getMessage();
            }
        }

        return $result;
    }
}
