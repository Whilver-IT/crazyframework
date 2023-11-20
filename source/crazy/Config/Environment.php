<?php

namespace Crazy\Config;

use \Bootstrap\App;

/**
 * 環境変数クラス
 */
class Environment {

    /**
     * 環境変数の設定情報(BASE_DIR/.env)を格納
     *
     * @var array
     */
    private static $settings = [];

    /**
     * 格納
     *
     * @return void
     */
    public static function init() : void {
        if(is_nullorempty(self::$settings)){
            $envFile = self::getEnvFile();
            if(file_exists($envFile) && is_file($envFile)){
                $file = array_values(array_filter(file($envFile)));
                foreach($file as $item){
                    $item = trim($item);
                    $keyPos = strpos($item, "=");
                    if($keyPos == -1){
                        continue;
                    }
                    $key = trim(substr($item, 0, $keyPos));
                    $value = trim(substr($item, $keyPos + 1));

                    if(substr($value, 0, 1) == "\""){
                        $value = substr($value, 1);
                    }
                    if(substr($value, -1, 1) == "\""){
                        $value = substr($value, 0, -1);
                    }

                    // 格納
                    if(is_nullorempty(self::$settings[$key])){
                        self::$settings[$key] = $value;
                    }
                }
            }
        }
        //echo "<pre>" . print_r(self::$settings, true) . "</pre>";
    }

    /**
     * .envファイル取得
     *
     * @return string
     */
    private static function getEnvFile(): string {
        $envFileDir = is_nullorempty(App::$BASE_DIR) ? implode(DIRECTORY_SEPARATOR, [__DIR__, "..", ".."]) : App::$BASE_DIR ;
        return $envFileDir . DIRECTORY_SEPARATOR . ".env";
    }

    
    /**
     * .envの内容を取得
     *
     * @param string $keyName       キー名
     * @param string|null $default  デフォルト値
     * @return string|null          設定値
     */
    public static function get(string $keyName, ?string $default = null): ?string {
        return is_nullorempty(self::$settings[$keyName]) ? $default : self::$settings[$keyName];
    }

}

Environment::init();
