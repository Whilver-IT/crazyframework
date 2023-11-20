<?php

namespace App\Repositoies;

use \App\Repositoies\RepositoryBase;
use \PDO;

/**
 * usersテーブル操作
 * 
 * usersテーブルを操作するのに必要な処理を記述
 */
class UsermasterRepository extends RepositoryBase {

    /**
     * 対象テーブル名
     */
    public const TABLE = "users";

    /**
     * 対象カラム名
     */
    public const COLUMNS = [
        "id",
        "password",
        "fname",
        "name",
        "fkana",
        "nkana",
        "byear",
        "bmonth",
        "bday",
        "zipcode",
        "address",
        "tel1",
        "tel2",
        "mail1",
        "mail2",
        "token",
        "status",
        "delflg",
        "masterflg",
        "created_user",
        "created_at",
        "updated_user",
        "updated_at",
    ];

    /**
     * ユーザ取得用SQL
     *
     * @param array $param
     * @return array
     */
    public function getUser(array $param): array {

        $sql_param = [
            'select' => self::COLUMNS,
            'from' => [
                self::TABLE,
            ],
        ];

        foreach(self::SQL_FACTOR as $factor){
            if(!is_nullorempty($param[$factor])){
                $sql_param[$factor] = $param[$factor];
            }
        }

        $sql = $this->simpleSelect($sql_param);
        $stmt = $this->db->prepare($sql);
        foreach(self::COLUMNS as $column){
            if(array_key_exists($column, $sql_param['where'])){
                switch($column){
                case "created_user":
                case "updated_user":
                    break;
                default:
                    $stmt->bindValue(":" . $column, $sql_param['where'][$column], PDO::PARAM_STR);
                    break;
                }
            }
        }
        $stmt->execute();
        $user = [];
        while(($row = $stmt->fetch(PDO::FETCH_ASSOC)) != false){
            if($is_need_passwd && password_verify($password, $row['password'])
            || !$is_need_passwd){
                $user[] = $row;
            }
        }
        $stmt->closeCursor();
        
        return $user;
    }

    /**
     * ユーザ作成
     *
     * @param array $user_info
     * @return void
     */ 
    public function createUser(array $user_info): void {
        $columns = [];
        foreach(self::COLUMNS as $column){
            if($column == "created_at" || $column == "updated_at"){
                $columns[$column] = "current_timestamp";
            } else {
                $columns[$column] = "";
            }
        }
        $sql_param = [
            'table' => self::TABLE,
            'columns' => $columns,
        ];

        $sql = $this->simpleInsert($sql_param);

        $stmt = $this->db->prepare($sql);

            foreach(UsermasterRepository::COLUMNS as $column){
                switch($column){
                case "password":
                    $stmt->bindValue(":" . $column, password_hash($user_info[$column], PASSWORD_DEFAULT), PDO::PARAM_STR);
                    break;
                case "token":
                    $stmt->bindValue(":" . $column, base64_encode(random_bytes(32)), PDO::PARAM_STR);
                    break;
                case "status":
                case "delflg":
                case "masterflg":
                    $stmt->bindValue(":" . $column, "0", PDO::PARAM_STR);
                    break;
                case "created_user":
                case "updated_user":
                    $value = is_nullorempty($_SESSION['login']['id']) ? $user_info['id'] : $_SESSION['login']['id'];
                    $stmt->bindValue(":" . $column, $value, PDO::PARAM_STR);
                    break;
                case "created_at":
                case "updated_at":
                    break;
                default:
                    $value = is_nullorempty($user_info[$column]) ? null : $user_info[$column];
                    $param = is_nullorempty($user_info[$column]) ? PDO::PARAM_NULL : PDO::PARAM_STR;
                    $stmt->bindValue(":" . $column, $value, $param);
                    break;
                }
            }
            $stmt->execute();
            $stmt->closeCursor();
    }
}