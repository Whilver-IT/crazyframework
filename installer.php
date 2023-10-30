<?php
date_default_timezone_set("Asia/Tokyo");
mb_internal_encoding("UTF-8");

/**
 * 雑多なメソッドをここで
 * クラスにも、別ファイルにもするまでもないと思ったのでここで
 */
trait utilTrait {

    /**
     * isset、empty、is_nullどれも判定が微妙なので
     *
     * @param mixed $variable
     * @return boolean
     */
    protected function isNullOrEmpty(mixed $variable): bool {
        if($variable === 0 || $variable === "0" || $variable === false){
            return false;
        }
        return empty($variable);
    }

    /**
     * 即時出力
     *
     * @param string $msg
     * @param boolean $is_line
     * @return void
     */
    protected function showMsg(string $msg, bool $is_line = true): void {
        ob_start();
        echo $msg . ($is_line ? "\n" : "");
        ob_end_flush();
    }
}

/**
 * インストーラ
 */
class Installer {

    use utilTrait;

    private $db_home = "";

    /**
     * コンストラクタ
     */
    public function __construct(){
    }

    /**
     * 初期処理
     *
     * @return void
     */
    public function init(): void {

        // databaseフォルダがあるか確認(無いことはありえないはずだが)
        $db_home = implode(DIRECTORY_SEPARATOR, [__DIR__, "database"]);
        if(!is_dir($db_home)){
            $this->showMsg("databaseフォルダが存在しません");
            exit(1);
        }
        $this->db_home = $db_home;
        if(!file_exists($this->db_home . DIRECTORY_SEPARATOR . "DBInfo.php")){
            $this->showMsg("database/DBInfo.phpファイルが存在しません");
            exit(1);
        }
        if(!file_exists($this->db_home . DIRECTORY_SEPARATOR . "Database.php")){
            $this->showMsg("database/Database.phpファイルが存在しません");
            exit(1);
        }
        require_once($this->db_home . DIRECTORY_SEPARATOR . "DBInfo.php");
        require_once($this->db_home . DIRECTORY_SEPARATOR . "Database.php");

        // .envファイルがあるか確認
        $is_make_env = true;
        $app_home = implode(DIRECTORY_SEPARATOR, [__DIR__, "source"]);
        $env_file = $app_home . DIRECTORY_SEPARATOR . ".env";
        $is_exist_env = file_exists($env_file);
        if($is_exist_env){
            $yes_no = "";
            while($yes_no == "") {
                $this->showMsg($app_home . DIRECTORY_SEPARATOR . ".envファイルが既に存在します");
                $this->showMsg(".envファイルを作成し直しますか!? [y/N] ", false);
                fscanf(STDIN, "%s", $yes_no);
                $yes_no = $this->isNullOrEmpty($yes_no) ? "n" : $yes_no;
                if(array_search(strtolower($yes_no), ["y", "n",], true) === false){
                    $yes_no = "";
                }
            }
            $is_make_env = strtolower($yes_no) == "y";
        }

        if($is_make_env){
            if($is_exist_env){
                unlink($env_file);
            }
            $settings = "DB_CONNECTION_01=main\n";
            foreach($this->getMakeEnvValue() as $key => $value) {
                $settings .= $key . "_01=\"" . $value . "\"\n";
            }
            file_put_contents($env_file, $settings);
        }
    }

    /**
     * envファイルの設定値を取得
     *
     * @return array
     */
    private function getMakeEnvValue(): array {

        $env_settings = [
            'DB_TYPE' => "",
            'DB_HOST' => "",
            'DB_PORT' => "",
            'DB_DATABASE' => "",
            'DB_USERNAME' => "",
            'DB_PASSWORD' => "",
        ];
        do {
            $this->showMsg("データベースは何を使用しますか!? [pgsql/mysql(mariadb)/mssql/oracle]: ", false);
            fscanf(STDIN, "%s", $env_settings['DB_TYPE']);
            switch(strtolower($env_settings['DB_TYPE'])){
            case "pgsql":
            case "mysql":
            case "mariadb":
            case "mssql":
            case "oracle":
                $env_settings['DB_TYPE'] = strtolower($env_settings['DB_TYPE'] == "mariadb" ? "mysql" : $env_settings['DB_TYPE']);
                break;
            default:
                $env_settings['DB_TYPE'] = "";
                break;
            }
        } while($this->isNullOrEmpty($env_settings['DB_TYPE']));

        $is_oracle = $env_settings['DB_TYPE'] == "oracle";
        $host_msg = "ホスト(IPアドレス)";
        $host_msg .= $is_oracle ? "TNS名を使用する場合は空" : "";

        do {
            $this->showMsg($host_msg . ": ", false);
            fscanf(STDIN, "%s", $env_settings['DB_HOST']);
            if (!$is_oracle && $this->isNullOrEmpty($env_settings['DB_HOST'])) {
                echo $host_msg . "が入力されていません\n";
            }
        } while (!$is_oracle && $this->isNullOrEmpty($env_settings['DB_HOST']));

        $port = -1;
        switch($env_settings["DB_TYPE"]){
        case "pgsql":
            $port = 5432;
            break;
        case "mysql":
        case "mariadb":
            $port = 3306;
            break;
        case "mssql":
            $port = 1433;
            break;
        case "oracle":
            $port = 1521;
            break;
        default:
            break;
        }
        do {
            echo "データベースポート番号 [default(empty)=" . $port ."]: ";
            fscanf(STDIN, "%d", $env_settings['DB_PORT']);
            if($this->isNullOrEmpty($env_settings['DB_PORT'])){
                $env_settings['DB_PORT'] = $port;
            } else {
                if(!is_numeric($env_settings['DB_PORT'])
                || ($env_settings['DB_PORT'] < 1024 || $env_settings['DB_PORT'] > 65535)){
                    $this->showMsg("ポート番号は1024以上、65535以下で設定してください");
                    $env_settings['DB_PORT'] = "";
                }
            }
        } while($this->isNullOrEmpty($env_settings['DB_PORT']));


        $this->showMsg("データベースユーザID: ", false);
        fscanf(STDIN, "%s", $env_settings['DB_USERNAME']);

        $this->showMsg("パスワード: ", false);
        fscanf(STDIN, "%s", $env_settings['DB_PASSWORD']);

        $dbname_msg = "データベース名";
        $dbname_msg .= $is_oracle ? "(TNS名を使用する場合はその名称)"  : '';
        do {
            $this->showMsg($dbname_msg . ": ", false);
            fscanf(STDIN, "%s", $env_settings['DB_DATABASE']);
            if ($this->isNullOrEmpty($env_settings['DB_DATABASE'])) {
                $this->showMsg($dbname_msg . "が入力されていません");
            }
        } while($this->isNullOrEmpty($env_settings['DB_DATABASE']));

        return $env_settings;
    }

    /**
     * 処理実行
     *
     * @return void
     */
    public function exec(): void {
        $db_param = (new DBInfo(implode(DIRECTORY_SEPARATOR, [__DIR__, "source"]), "01"))->getDBInfo();
        $db = $this->getDatabaseObject($db_param);
        if($db == null){
            $this->showMsg("使用できないデータベースが設定されています");
            exit(1);
        }
        foreach($db::TABLE_INFO as $info){
            if(!$db->checkExistsTable($info['table'])){
                $db->createTable($info['table']);
            }

            if($this->isNullOrEmpty($info['data'])){
                continue;
            }

            if($db->checkDataCount($info['table']) > 0){
                $this->showMsg("既にデータが入っているため処理をスキップします(" . $info['table'] . ")");
            } else {
                $db->insertData($this->db_home, $info);
            }
        }
        $this->showMsg("インストールが完了しました");
    }

    /**
     * DBオブジェクト取得
     *
     * @param array $db_param
     * @return Database|null
     */
    private function getDatabaseObject(array $db_param): ?Database {

        $db_object = null;

        switch($db_param['type']){
        case "mysql":
        case "mariadb":
            require_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "database", "mariadb", "MariaDB.php"]));
            $db_object = new MariaDB($db_param);
            break;
        case "pgsql":
            require_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "database", "postgresql", "PostgreSQL.php"]));
            $db_object = new PostgreSQL($db_param);
            break;
        case "mssql":
            require_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "database", "mssql", "Mssql.php"]));
            $db_object = new Mssql($db_param);
            break;
        case "oracle":
            require_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "database", "oracle", "Oracle.php"]));
            $db_object = new Oracle($db_param);
            break;
        default:
            break;
        }

        return $db_object;
    }
}

$installer = new Installer();
$installer->init();
$installer->exec();
