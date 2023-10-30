<?php

require_once(implode(DIRECTORY_SEPARATOR,  [__DIR__, "..", "DatabaseInterface.php"]));
require_once(implode(DIRECTORY_SEPARATOR,  [__DIR__, "..", "Database.php"]));

/**
 * インストーラMariaDB(MySQL)用クラス
 */
class MariaDB extends Database implements DatabaseInterface {

    /**
     * DSN取得
     *
     * @return string
     */
    public function getDsn(): string {
        return "mysql:host=" . $this->db_param['host'] . ";port=" . $this->db_param['port'] . ";" . $this->getDsnDatabaseKeyword() . "=" . $this->db_param['dbname'] . ";";
    }

    /**
     * PDOのDSNデータベース名キーワード設定
     *
     * @return string
     */
    public function getDsnDatabaseKeyword(): string {
        return "dbname";
    }

    /**
     * データベース存在チェック
     *
     * @param string $db_name
     * @return boolean
     */
    public function checkExistDatabase(): bool {
        $sql = <<<SQL
SELECT
      count(*) as cnt
FROM
      information_schema.schemata
WHERE
    schema_name = :schema_name
SQL;
        $result = false;
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":schema_name", $this->db_param['dbname'], PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            if($row === false){
                $this->showMsg("データベース情報の取得に失敗しました");
                exit(1);
            }
            $result = $row['cnt'] == 1;
        } catch(Exception $e) {
            $this->showMsg("データベース情報の取得に失敗しました(" . $e->getMessage() . ")");
            exit(1);
        }

        return $result;
    }

    /**
     * テーブル存在チェック
     *
     * @param string $table_name
     * @return boolean
     */
    public function checkExistsTable(string $table_name): bool {
        $sql = <<<SQL
SELECT
      count(*) as cnt
FROM
      information_schema.tables
WHERE
    table_schema = :table_schema
AND
    table_name = :table_name
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":table_schema", $this->db_param['dbname'], PDO::PARAM_STR);
        $stmt->bindValue(":table_name", $table_name, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $row['cnt'] == 1;
    }
}