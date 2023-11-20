<?php

namespace App\Repositoies;

use \Crazy\Database\DB;
use \PDO;

/**
 * リポジトリベース抽象クラス
 * 
 * リポジトリのベースとなる処理を記述
 * SQLの処理をここで記述
 */
abstract class RepositoryBase {

    /**
     * データベースオブジェクト
     *
     * @var PDO
     */
    protected PDO $db;

    /**
     * コンストラクタ
     */
    public function __construct(PDO $db = null){
        $this->db = $db == null ? DB::getConnection() : $db;
    }

    /**
     * SQLで必要となる要素を記述
     */
    public const SQL_FACTOR = [
        'where',
        'whereColumn',
        'order',
    ];

    /**
     * 単純な単結合SELECTクエリ作成
     *
     * @param array $select_param
     * @return string
     */
    public function simpleSelect(array $select_param): string {
        $select = "";
        if(is_nullorempty($select_param['select'])){
            $select .= "SELECT\n\t  *";
        } elseif(is_array($select_param['select'])){
            foreach($select_param['select'] as $column){
                $select .= is_nullorempty($select) ? "SELECT\n\t  " : "\n\t, ";
                $select .= $column;
            }
        } else {
            $select .= "SELECT\n\t  " . $select_param['select'];
        }

        $from = "";
        foreach($select_param['from'] as $table){
            $from .= is_nullorempty($from) ? "FROM\n\t  " : "\n\t, ";
            $from .= $table;
        }

        $where_column = is_nullorempty($select_param['whereColumn']) ? "" : $this->simpleWhereColumn($select_param['whereColumn']);
        $where = is_nullorempty($select_param['where']) ? "" : $this->simpleWhere($select_param['where'], strlen($where_column) > 0);
        $order = is_nullorempty($select_param['order']) ? "" : $this->order($select_param['order']);

        return <<<SQL
{$select}
{$from}
{$where_column}
{$where}
{$order}
SQL;
    }

    /**
     * カラムに対してのwhere句作成
     *
     * @param array $where_col_param
     * @param boolean $is_and
     * @return string
     */
    public function simpleWhereColumn(array $where_col_param, bool $is_and = false): string {
        $where = "";
        foreach($where_col_param as $where_col){
            $keyword = (is_nullorempty($where) ? "" : "\n") . ($is_and || !is_nullorempty($where)) ? " AND " : "WHERE";
            $operator = (count($where_col) == 2) ? "=" : $where_col[1];
            $column = [
                $where_col[0],
                (count($where_col) == 2) ? $where_col[1] : $where_col[2],
            ];
            $where .= <<<WHERE
{$keyword} {$column[0]} {$operator} {$column[1]}
WHERE;
        }

        return $where;
    }

    /**
     * 単純なwhere構成クエリ作成
     *
     * @param array $where_param
     * @param boolean $is_and
     * @return string
     */
    public function simpleWhere(array $where_param, bool $is_and = false): string {
        $where = "";
        foreach($where_param as $column => $item){
            $keyword = (is_nullorempty($where) ? "" : "\n") . ($is_and || !is_nullorempty($where)) ? " AND " : "WHERE";
            $operator = is_array($item) ? (count($item) >= 2 ? $item[0] : "=") : "=";
            $value = ":" . $column;
            $where .= <<<WHERE
{$keyword} {$column} {$operator} {$value}
WHERE;
        }
        return $where;
    }

    /**
     * ORDER BY句作成
     *
     * @param array $orders
     * @return string
     */
    public function order(array $orders): string {
        $order = "";
        foreach($orders as $col_name => $asc_desc){
            $connect = is_nullorempty($order) ? "ORDER BY" : "\n\t, ";
            $order .= <<<ORDER
{$connect} {$col_name} {$asc_desc}
ORDER;
        }
        return $order;
    }

    /**
     * 単純なINSERTクエリ生成
     *
     * @param array $insert_param
     * @return string
     */
    public function simpleInsert(array $insert_param): string {
        $columns = "";
        $values = "";
        foreach($insert_param['columns'] as $column_name => $column_value){
            $prefix = is_nullorempty($columns) ? " " : ",";
            $columns .= <<<COLUMN
    {$prefix} {$column_name}

COLUMN;
            $value = is_nullorempty($column_value) ? ":" . $column_name : $column_value;
            $values .= <<<VALUES
    {$prefix} {$value}
    
VALUES;
        }
        $insert = <<<SQL
insert into {$insert_param['table']} (
    {$columns}
) values (
    {$values}
)
SQL;

        return $insert;
    }

}
