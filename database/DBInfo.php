<?php
/**
 * .envからDB情報取得用クラス
 */
class DBInfo {

    use utilTrait;

    /**
     * アプリケーションの基準となるフォルダ(空でないケースで動かすことがあるのかは不明だが)
     *
     * @var string
     */
    private string $appHome = "";

    /**
     * .envファイルのDB情報の何番を使用するか(デフォルト01番)
     *
     * @var string
     */
    private string $connNum = "01";

    /**
     * コンストラクタ
     *
     * @param string $appHome
     * @param string $connNum
     */
    public function __construct(string $appHome = "", string $connNum = "") {
        $this->appHome = $this->isNullOrEmpty($appHome) ? implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "source"]) : $appHome;
        if(substr($this->appHome, -1, 1) == DIRECTORY_SEPARATOR){
            $this->appHome = substr($this->appHome, 0, -1);
        }
        $this->connNum = $this->isNullOrEmpty($connNum) ? $this->connNum : $connNum;
    }

    /**
     * DB情報取得
     *
     * @return array
     */
    public function getDBInfo(): array {
        $dbInfo = [
            "type" => "",
            "host" => "",
            "port" => "",
            "dbname" => "",
            "user" => "",
            "pass" => "",
        ];
        $filePath = $this->appHome . DIRECTORY_SEPARATOR . ".env";
        if(!file_exists($filePath)){
            $this->showMsg(".envファイルが存在しません。.envファイルを確認してください");
            exit(1);
        }

        $file = file($filePath);
        foreach($file as $line){
            if(preg_match("/^\s*#/", $line) == 1){
                continue;
            }

            $keyValue = explode("=", str_replace(["\r", "\n"],"", $line));
            $key = array_shift($keyValue);
            if(!(substr($key, 0, 3) == "DB_" && substr($key, -3, 3) == "_" . $this->connNum)){
                continue;
            }

            $value = implode("=", $keyValue);
            if(substr($value, 0, 1) == "\""){
                $value = substr($value, 1);
            }
            if(substr($value, -1, 1) == "\""){
                $value = substr($value, 0, -1);
            }

            switch(substr($key, 3, -3)){
            case "TYPE":
                $dbInfo['type'] = $value;
                break;
            case "HOST":
                $dbInfo['host'] = $value;
                break;
            case "PORT":
                $dbInfo['port'] = $value;
                break;
            case "DATABASE":
                $dbInfo['dbname'] = $value;
                break;
            case "USERNAME":
                $dbInfo['user'] = $value;
                break;
            case "PASSWORD":
                $dbInfo['pass'] = $value;
                break;
            }
        }

        return $dbInfo;
    }
}