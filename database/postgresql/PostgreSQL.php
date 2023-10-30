<?php

require_once(implode(DIRECTORY_SEPARATOR,  [__DIR__, "..", "DatabaseInterface.php"]));
require_once(implode(DIRECTORY_SEPARATOR,  [__DIR__, "..", "Database.php"]));

/**
 * インストーラPostgreSQL用クラス
 */
class PostgreSQL extends Database implements DatabaseInterface {

    /**
     * DSN取得
     *
     * @return string
     */
    public function getDsn(): string {
        return "pgsql:dbname=" . $this->db_param['dbname'] . ";host=" . $this->db_param['host'] . ";port=" . $this->db_param['port'];
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
     * @return boolean
     */
    public function checkExistDatabase(): bool {
        $sql = <<<SQL
SELECT
      count(*) as cnt
FROM
      pg_database
WHERE
    datname = :datname
SQL;
        $result = false;
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":datname", $this->db_param['dbname'], PDO::PARAM_STR);
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
    table_name = :table_name
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":table_name", $table_name, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $row['cnt'] == 1;
    }
}