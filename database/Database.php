<?php

class Database {

    use utilTrait;

    /**
     * 対象テーブル
     */
    public const TABLE_INFO = [
        [
            'table' => "offices_zipinfo",
            'data' => "jigyosyo",
            "download" => "https://www.post.japanpost.jp/zipcode/dl/jigyosyo/zip/jigyosyo.zip",
        ],
        [
            'table' => "prefectures",
            'data' => "prefectures",
        ],
        [
            'table' => "users",
            'data' => "",
        ],
        [
            'table' => "zipinfo",
            'data' => "utf_all",
            "download" => "https://www.post.japanpost.jp/zipcode/dl/utf/zip/utf_all.zip",
        ],
    ];

    /**
     * データベース設定
     *
     * @var array
     */
    protected array $db_param;

    /**
     * データベースオブジェクト
     *
     * @var PDO
     */
    protected ?PDO $db;

    /**
     * コンストラクタ
     *
     * @param array $db_param
     */
    public function __construct(array $db_param) {
        $this->db_param = $db_param;
        // getDsnは継承先のクラスのメソッドで記載する
        $dsn = $this->getDsn();
        $options = [
            PDO::ATTR_CASE => PDO::CASE_LOWER,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ];
        try {
            // フレームワークのデータベースが存在しないかもしれないので、
            // 一旦規定のDBに接続
            $dsn_db = $this->getDsnDatabaseKeyword() . "=" . $this->db_param['dbname'] . ";";
            $replace_db = "";
            switch($this->db_param['type']){
            case "pgsql":
                $replace_db = $this->getDsnDatabaseKeyword() . "=postgres;";
                break;
            case "oracle":
                $replace_db = $dsn_db;
                break;
            default:
                break;
            }
            $this->db = new PDO(str_replace($dsn_db, $replace_db, $dsn), $this->db_param['user'], $this->db_param['pass'], $options);
        } catch(Exception $e){
            $this->showMsg("データベースの接続に失敗しました(" . $e->getMessage() . ")");
            exit(1);
        }
        if(!$this->checkExistDatabase()){
            $this->createDatabase();
        }
        $this->db = null;
        $this->db = new PDO($dsn, $this->db_param['user'], $this->db_param['pass'], $options);
    }

    /**
     * DSNのデータベースを指定するキーワードを取得
     * dbnameでない場合は、継承先のクラスでoverrideすること
     */
    protected function getDsnDatabaseKeyword(): string {
        return "dbname";
    }

    /**
     * データベース作成
     *
     * @param string $db_name
     * @return void
     */
    protected function createDatabase(): void {
        try {
            $stmt = $this->db->prepare("CREATE DATABASE " . $this->db_param['dbname']);
            $stmt->execute();
            $stmt->closeCursor();
        } catch(Exception $e){
            $this->showMsg("データベースの作成に失敗しました(" . $e->getMessage() . ")");
            exit(1);
        }
    }

    /**
     * テーブル作成
     *
     * @param string $table_name
     * @return void
     */
    public function createTable(string $table_name): void {
        $db_type = $this->db_param['type'] == "mysql" ? "mariadb" : $this->db_param['type'];
        $filename = implode(DIRECTORY_SEPARATOR, [__DIR__, ($db_type == "pgsql" ? "postgresql" : $db_type), "tables", $table_name . ".sql"]);
        if(!file_exists($filename)){
            $this->showMsg("該当のテーブルが存在しません(" . $filename . ")");
            exit(1);
        }

        $sqls = $this->getSqlsFromSqlFile($filename);
        foreach($sqls as $sql){
            if($db_type == "mssql"){
                // mssqlの場合はgoの行を無視させる
                $lines = explode("\n", $sql);
                $sql = "";
                foreach($lines as $line){
                    $sql .= (trim($line) == "go") ? "" : $line . "\n";
                }
                if(trim($sql) == ""){
                    continue;
                }
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stmt->closeCursor();
        }
    }

    /**
     * SQLファイルからSQLを抜き出す
     *
     * @param string $sql_file
     * @return array
     */
    private function getSqlsFromSqlFile(string $sql_file): array {
        $content = $this->removeCommentFromSqlFile(str_replace(["\r\n", "\r"], "\n", @file_get_contents($sql_file)));
        $search_start = 0;
        $sqls = [];
        $sql = "";
        while(true){
            $quot_pos = strpos($content, "'", $search_start);
            $semi_pos = strpos($content, ";", $search_start);

            if($quot_pos == false && $semi_pos === false){
                $sql .= substr($content, $search_start);
                break;
            }

            if($quot_pos !== false && $semi_pos != false && $quot_pos < $semi_pos){
                $next_start_quot_pos = $quot_pos + 1;
                while(true){
                    $quot_close_pos = strpos($content, "'", $next_start_quot_pos);
                    $quot_next_pos = strpos($content, "''", $next_start_quot_pos);
                    if($quot_close_pos === false || $quot_next_pos === false || $quot_close_pos < $quot_next_pos){
                        break;
                    }
                    $next_start_quot_pos = $quot_next_pos + 2;
                }

                if($quot_close_pos === false){
                    $sqls[] = substr($content, $search_start);
                    break;
                }
    
                $sql .= substr($content, $search_start, $quot_close_pos + 1 - $search_start);
                $search_start = $quot_close_pos + 1;
                continue;
            }

            if($semi_pos !== false){
                $sql .= substr($content, $search_start, $semi_pos - $search_start);
                $sqls[] = $sql;
                $sql = "";
                $search_start = $semi_pos + 1;
            }
        }

        if(strlen($sql) > 0){
            $sqls[] = $sql;
        }

        $sqls = array_map(
            function ($sql) { return trim($sql); },
            $sqls
        );

        return array_filter(
            $sqls,
            function ($sql) {
                return strlen($sql) > 0;
            }
        );
    }

    /**
     * SQLファイルのコメントを除去
     *
     * @param string $sql_contents
     * @return string
     */
    private function removeCommentFromSqlFile(string $sql_contents): string {
        $content = $sql_contents;
        $search_start = 0;
        while(true) {

            $quot_pos = strpos($content, "'", $search_start);
            $line_comment_pos = strpos($content, "--", $search_start);
            $multi_comment_pos = strpos($content, "/*", $search_start);

            if($quot_pos === false && $line_comment_pos === false && $multi_comment_pos === false){
                // どれもヒットしなければ何もしない
                break;
            }

            if($quot_pos !== false){
                $min_pos = min(
                    $quot_pos,
                    $line_comment_pos === false ? $quot_pos : $line_comment_pos,
                    $multi_comment_pos === false ? $quot_pos : $multi_comment_pos,
                );
                if($quot_pos == $min_pos){
                    // シングルクォートが最も手前なので、閉じ位置を確認(但しシングルクォート中のシングルクォート「''」は無視)
                    $next_start_quot_pos = $quot_pos + 1;
                    while(true){
                        $quot_close_pos = strpos($content, "'", $next_start_quot_pos);
                        $quot_next_pos = strpos($content, "''", $next_start_quot_pos);
                        if($quot_close_pos === false
                        || $quot_next_pos === false
                        || $quot_close_pos < $quot_next_pos){
                            break;
                        }
                        $next_start_quot_pos = $quot_next_pos + 1;
                    }
                    if(!$quot_close_pos){
                        // シングルクォート開きっぱなしとみなし何もしない
                        break;
                    }
                    $search_start = $quot_close_pos + 1;
                    continue;
                }
            }

            if(($line_comment_pos !== false && $multi_comment_pos !== false && $line_comment_pos < $multi_comment_pos)
            || ($line_comment_pos !== false && $multi_comment_pos === false)){
                $line_comment_end_pos = strpos($content, "\n", $line_comment_pos + 2);
                if($line_comment_end_pos === false){
                    $content = substr($content, 0, $line_comment_pos);
                } else {
                    $first_part = substr($content, 0, $line_comment_pos);
                    $left_part = substr($content, $line_comment_end_pos);
                    $content = $first_part . $left_part;
                }
                $search_start = 0;
            } else {
                $multi_comment_end_pos = strpos($content, "*/", $multi_comment_pos);
                if($multi_comment_end_pos === false){
                    $search_start = $multi_comment_pos + 2;
                } else {
                    $first_part = substr($content, 0, $multi_comment_pos);
                    $left_part = substr($content, $multi_comment_end_pos + 2);
                    $content = $first_part . $left_part;
                    $search_start = 0;
                }
            }

        }

        return $content;
    }

    /**
     * テーブルにデータがあるかどうか確認
     *
     * @param string $table
     * @return integer
     */
    public function checkDataCount(string $table): int {
        $stmt = $this->db->prepare("SELECT count(*) as cnt FROM " . $table);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        if($row === false){
            $this->showMsg("テーブルの件数取得に失敗しました(" . $table . ")");
            exit(1);
        }
        return $row['cnt'];
    }

    /**
     * CSVからテーブルにデータ挿入
     *
     * @param string $database_path
     * @param array $table_info
     * @return void
     */     
    public function insertData(string $database_path, array $table_info): void {

        if(!isset($table_info['data']) || strlen($table_info['data']) == 0){
            return;
        }

        $filename = implode(DIRECTORY_SEPARATOR, [__DIR__, "data", $table_info['data'] . ".csv"]);
        if(!file_exists($filename)){
            if($this->isNullOrEmpty($table_info['download'] ?? null)
            || !$this->csvDownload($database_path, $table_info)) {
                $this->showMsg("データファイルが存在しなかったのでスキップします(" . $table_info['data'] . ".csv)");
                return;
            }
        }

        switch($table_info['data']){
        case "jigyosyo":
        case "prefectures":
        case "utf_all":
            $this->{"insert" . str_replace(["_"], "", ucfirst(strtolower($table_info['data'])))}($filename);
            break;
        default:
            $this->showMsg("対象以外のデータが設定されたのでスキップします(" . $table_info['data'] . ")");
            break;
        }
    }

    /**
     * CSVダウンロード
     *
     * @param string $database_path
     * @param array $table_info
     * @return boolean
     */
    private function csvDownload(string $database_path, array $table_info): bool {

        if(!class_exists("ZipArchive")){
            $this->showMsg("ZipArchiveクラスを利用できないので処理をスキップします");
            return false;
        } elseif ($this->isNullOrEmpty($table_info['download'] ?? null)){
            $this->showMsg("ダウンロードリンクがないので処理をスキップします");
            return false;
        }

        $header = [
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36",
        ];
        $context = [
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", $header),
            ],
        ];
        $this->showMsg($table_info['download'] . "をダウンロード");

        $is_download_zip = false;
        if($zip = @file_get_contents($table_info['download'], false, stream_context_create($context))){
            try {
                $this->showMsg("ダウンロード完了");
                $zipDir = $database_path . DIRECTORY_SEPARATOR . "data";
                $file = $zipDir . bin2hex(random_bytes(16)) . ".zip";
                if(@file_put_contents($file, $zip) === false){
                    $this->showMsg("zipファイルの保存に失敗しました");
                    return $is_download_zip;
                } else {
                    $zip_archive = new ZipArchive;
                    if($zip_archive->open($file) && $zip_archive->count() > 0){
                        if(($contents = $zip_archive->getFromIndex(0)) === false){
                            $this->showMsg("csvファイルの取得に失敗しました");
                        } else {
                            $is_download_zip = @file_put_contents($zipDir . DIRECTORY_SEPARATOR . $table_info['data'] . ".csv", $contents) === false ? false : true;
                        }
                    }
                    @unlink($file);
                }
            } catch(Throwable $th){
                $this->showMsg("zipファイルの操作に失敗しました(" . $th->getMessage() . ")");
            }
        } else {
            if(!$this->isNullOrEmpty($http_response_header)
            && count($http_response_header)) {
                $status = explode(' ', $http_response_header[0]);
                $this->showMsg("zipのダウンロードに失敗しました(url=" . $url . " status_code=" . $status[1] . ")");
            } else {
                $this->showMsg("zipのダウンロードに失敗しました(url=" . $url . ")");
            }
        }

        return $is_download_zip;
    }

    /**
     * CSVからoffices_zipinfoテーブルにデータ挿入
     *
     * @param string $filename
     * @return void
     */
    private function insertJigyosyo(string $filename): void {
        try {
            $this->showMsg("事業所郵便番号マスタ(offices_zipinfo)にデータを挿入します");
            $file = file($filename);
            $all_count = count($file);
            $this->showMsg("全データ数：" . $all_count . "件");
            $is_begin = false;
            $this->db->beginTransaction();
            $is_begin = true;
            $stmt = $this->db->prepare($this->insertJigyosyoSql());
            $id = 0;
            foreach($file as $line){
                $line = rtrim(mb_convert_encoding($line, "UTF-8", "SJIS"));
	            $data = explode(",", $line);
	            $stmt->bindValue(":id", ++$id, PDO::PARAM_INT);
	            $stmt->bindValue(":pref_cd", substr(trim($data[0]), 0, 2), PDO::PARAM_STR);
	            $stmt->bindValue(":city_cd", substr(trim($data[0]), 2), PDO::PARAM_STR);
	            $stmt->bindValue(":city", str_replace("\"", "", trim($data[4])), PDO::PARAM_STR);
	            if($this->isNullOrEmpty(trim($data[5]))){
		            $stmt->bindValue(":street", null, PDO::PARAM_NULL);
	            } else {
		            $stmt->bindValue(":street", str_replace("\"", "", trim($data[5])), PDO::PARAM_STR);
	            }
	            if($this->isNullOrEmpty(trim($data[6]))){
		            $stmt->bindValue(":other", null, PDO::PARAM_NULL);
	            } else {
		            $stmt->bindValue(":other", str_replace("\"", "", trim($data[6])), PDO::PARAM_STR);
	            }
	            $stmt->bindValue(":zip", str_replace("\"", "", trim($data[7])), PDO::PARAM_STR);
	            $stmt->bindValue(":company_name", str_replace("\"", "", trim($data[2])), PDO::PARAM_STR);
	            $stmt->bindValue(":company_kana", str_replace("\"", "", trim($data[1])), PDO::PARAM_STR);	
	            $stmt->bindValue(":created_user", "system", PDO::PARAM_STR);
	            $stmt->bindValue(":updated_user", "system", PDO::PARAM_STR);

	            $stmt->execute();

                if($id % 1000 == 0 || $all_count == $id){
                    $this->showMsg($id . "件登録 / " . $all_count . "件");
                }
            }
            $stmt->closeCursor();
            $this->db->commit();
            $this->showMsg("データ登録が完了しました");
        } catch(Exception $e){
            if($is_begin){
                $this->db->rollback();
            }
            $this->showMsg("データ挿入中にエラーが発生したので処理を中止します(" . $e->getMessage() . ")");
            exit(1);
        }
    }

    /**
     * offices_zipinfoテーブル挿入SQL
     *
     * @return string
     */
    private function insertJigyosyoSql(): string {
        return <<<SQL
INSERT INTO offices_zipinfo (
      id
    , zip
    , pref_cd
    , city_cd
    , city
    , street
    , other
    , company_name
    , company_kana
    , created_user
    , created_at
    , updated_user
    , updated_at
) VALUES (
      :id
    , :zip
    , :pref_cd
    , :city_cd
    , :city
    , :street
    , :other
    , :company_name
    , :company_kana
    , :created_user
    , current_timestamp
    , :updated_user
    , current_timestamp
)
SQL;
    }

    /**
     * CSVからprefecturesテーブルにデータ挿入
     *
     * @param string $filename
     * @return void
     */
    private function insertPrefectures(string $filename): void {
        try {
            $this->showMsg("都道府県マスタ(prefectures)にデータを挿入します");
            $file = file($filename);
            array_splice($file, 0, 1);
            $all_count = count($file);
            $this->showMsg("全データ数：" . $all_count . "件");
            $is_begin = false;
            $this->db->beginTransaction();
            $is_begin = true;
            $stmt = $this->db->prepare($this->insertPrefecturesSql());
            $count = 0;
            foreach($file as $line){
                $count++;
                $line = rtrim(mb_convert_encoding($line, "UTF-8", "SJIS"));
	            $data = explode(",", $line);
	            $pref_name = trim($data[1]) == "北海道" ? trim($data[1]) : mb_substr(trim($data[1]), 0, -1);
	            $stmt->bindValue(":cd", trim($data[0]), PDO::PARAM_STR);
	            $stmt->bindValue(":name", $pref_name, PDO::PARAM_STR);
	            if($pref_name == "北海道"){
		            $stmt->bindValue(":suffix", null, PDO::PARAM_NULL);
	            } else {
		            $stmt->bindValue(":suffix", mb_substr(trim($data[1]), -1, 1), PDO::PARAM_STR);
	            }
	            $stmt->bindValue(":created_user", "system", PDO::PARAM_STR);
	            $stmt->bindValue(":updated_user", "system", PDO::PARAM_STR);
	            $stmt->execute();
                $this->showMsg($count . "件登録 / " . $all_count . "件");
            }
            $stmt->closeCursor();
            $this->db->commit();
            $this->showMsg("データ登録が完了しました");
        } catch(Exception $e){
            if($is_begin){
                $this->db->rollback();
            }
            $this->showMsg("データ挿入中にエラーが発生したので処理を中止します(" . $e->getMessage() . ")");
            exit(1);
        }
    }

    /**
     * prefecturesテーブル挿入SQL
     *
     * @return string
     */
    private function insertPrefecturesSql(): string {
        return <<<SQL
INSERT INTO prefectures (
	  cd
	, name
	, suffix
	, created_user
	, created_at
	, updated_user
	, updated_at
) VALUES (
	  :cd
	, :name
	, :suffix
	, :created_user
	, current_timestamp
	, :updated_user
	, current_timestamp
)
SQL;
    }

    /**
     * CSVからzipinfoテーブルにデータ挿入
     *
     * @param string $filename
     * @return void
     */
    private function insertUtfall(string $filename): void {
        try {
            $this->showMsg("郵便番号マスタ(zipinfo)にデータを挿入します");
            $file = file($filename);
            $all_count = count($file);
            $this->showMsg("全データ数：" . $all_count . "件");
            $is_begin = false;
            $this->db->beginTransaction();
            $is_begin = true;
            $stmt = $this->db->prepare($this->insertUtfallSql());
            $id = 0;
            foreach($file as $line){

                $data = explode(",", rtrim($line));
	
	            $stmt->bindValue(":id", ++$id, PDO::PARAM_INT);
	            $stmt->bindValue(":pref_cd", substr(trim($data[0]), 0, 2), PDO::PARAM_STR);
	            $stmt->bindValue(":city_cd", substr(trim($data[0]), 2), PDO::PARAM_STR);
	            $stmt->bindValue(":city", str_replace("\"", "", trim($data[7])), PDO::PARAM_STR);
	            $stmt->bindValue(":city_kana", str_replace("\"", "", trim($data[4])), PDO::PARAM_STR);
	            if($this->isNullOrEmpty(trim($data[8]))){
		            $stmt->bindValue(":street", null, PDO::PARAM_NULL);
	            } else {
		            $stmt->bindValue(":street", str_replace("\"", "", trim($data[8])), PDO::PARAM_STR);
	            }
	            if($this->isNullOrEmpty(trim($data[5]))){
		            $stmt->bindValue(":street_kana", null, PDO::PARAM_NULL);
	            } else {
		            $stmt->bindValue(":street_kana", str_replace("\"", "", trim($data[5])), PDO::PARAM_STR);
	            }
	            $stmt->bindValue(":zip", str_replace("\"", "", trim($data[2])), PDO::PARAM_STR);
	            $stmt->bindValue(":created_user", "system", PDO::PARAM_STR);
	            $stmt->bindValue(":updated_user", "system", PDO::PARAM_STR);

	            $stmt->execute();

                if($id % 1000 == 0 || $all_count == $id){
                    $this->showMsg($id . "件登録 / " . $all_count . "件");
                }
            }
            $stmt->closeCursor();
            $this->db->commit();
            $this->showMsg("データ登録が完了しました");
        } catch(Exception $e){
            if($is_begin){
                $this->db->rollback();
            }
            $this->showMsg("データ挿入中にエラーが発生したので処理を中止します(" . $e->getMessage() . ")");
            exit(1);
        }        
    }

    /**
     * zipinfoテーブル挿入SQL
     *
     * @return string
     */
    private function insertUtfallSql(): string {
        return <<<SQL
INSERT INTO zipinfo (
	  id
	, pref_cd
	, city_cd
	, city
	, city_kana
	, street
	, street_kana
	, zip
	, created_user
	, created_at
	, updated_user
	, updated_at
) VALUES (
	  :id
	, :pref_cd
	, :city_cd
	, :city
	, :city_kana
	, :street
	, :street_kana
	, :zip
	, :created_user
	, current_timestamp
	, :updated_user
	, current_timestamp
)
SQL;
    }
}