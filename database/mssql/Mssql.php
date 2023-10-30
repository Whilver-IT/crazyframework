<?php

require_once(implode(DIRECTORY_SEPARATOR,  [__DIR__, "..", "DatabaseInterface.php"]));
require_once(implode(DIRECTORY_SEPARATOR,  [__DIR__, "..", "Database.php"]));

/**
 * インストーラMSSQL(Microsoft SQL Server)用クラス
 */
class Mssql extends Database implements DatabaseInterface {

    /**
     * DSN取得
     *
     * @return string
     */
    public function getDsn(): string {
        return "sqlsrv:Server=" . $this->db_param['host'] . "," . $this->db_param['port'] . ";" . $this->getDsnDatabaseKeyword() . "=" . $this->db_param['dbname'] . ";TrustServerCertificate=1";
    }

    /**
     * PDOのDSNデータベース名キーワード設定
     *
     * @return string
     */
    public function getDsnDatabaseKeyword(): string {
        return "Database";
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
      sys.databases
WHERE
    name = :name
SQL;
        $result = false;
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":name", $this->db_param['dbname'], PDO::PARAM_STR);
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
      sys.tables
WHERE
    name = :name
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":name", $table_name, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        return $row['cnt'] == 1;
    }
}
