<?php

require_once(implode(DIRECTORY_SEPARATOR,  [__DIR__, "..", "DatabaseInterface.php"]));
require_once(implode(DIRECTORY_SEPARATOR,  [__DIR__, "..", "Database.php"]));

/**
 * インストーラOracle用クラス
 */
class Oracle extends Database implements DatabaseInterface {

    /**
     * DSN設定
     */
    public function getDsn(): string {
        $dsn = "oci:dbname=";
        if(isset($this->db_param['host']) && strlen($this->db_param['host'])){
            $dsn .= "//" . $this->db_param['host'] . ":" . $this->db_param['port'] . "/" . $this->db_param['dbname'];
        } else {
            $dsn .= $this->db_param['dbname'];
        }
        $dsn .= ";charset=AL32UTF8";
	$dsn = "oci:dbname=foo;charset=AL32UTF8";
        return $dsn;
    }

    /**
     * PDOのDSNデータベース名キーワード設定
     * Oracleは「dbname」だが、基底のDBに接続しないので空をセット
     *
     * @return string
     */
    public function getDsnDatabaseKeyword(): string {
        return "";
    }

    /**
     * データベース存在チェック
     * Oracleの場合は存在する前提とする
     *
     * @return boolean
     */
    public function checkExistDatabase(): bool {
        return true;
    }

    /**
     * テーブルの存在チェック
     *
     * @param string $table_name
     * @return boolean
     */
    public function checkExistsTable(string $table_name): bool {
        $sql = <<<SQL
SELECT
    COUNT(*) as cnt
FROM
    USER_TABLES
WHERE
    TABLE_NAME = :table_name
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":table_name", strtoupper($table_name), PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $row['cnt'] == 1;
    }
}
