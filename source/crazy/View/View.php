<?php

namespace Crazy\View;

use \Bootstrap\App;

/**
 * HTMLビュー
 * HTMLビューに関する記述を行う
 */
class View {

    /**
     * ビューフォルダの基準ディレクトリ
     *
     * @var string
     */
    private static string $current = "";

    /**
     * コンストラクタ
     */
    private function __construct(){}

    /**
     * ビューフォルダをセット
     *
     * @param string $current
     * @return void
     */
    public static function setCurrentViewFolder(string $current = ""): void {
        $current_dir = "";
        $base_dir = is_nullorempty(App::$BASE_DIR) ? realpath(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "config"])) : App::$BASE_DIR;
        $folder_list = include(implode(DIRECTORY_SEPARATOR, [$base_dir, "config", "view.php"]));
        foreach($folder_list as $folderName){
            if($folderName == $current){
                $current_dir = $current;
                break;
            }
        }
        self::$current = implode(DIRECTORY_SEPARATOR, [$base_dir, "resources", is_nullorempty($current_dir) ? $folder_list[0] : $current_dir]);
    }

    /**
     * ビューからHTMLを生成
     *
     * @param string $blade
     * @param array $param
     * @return string
     */
    public static function make(string $blade, array $param): string {
        if(is_nullorempty(self::$current)){
            self::setCurrentViewFolder("");
        }
        foreach($param as $key => $value){
            ${$key} = $value;
        }
        ob_end_clean();
        ob_start();

        include(implode(DIRECTORY_SEPARATOR, [self::$current, str_replace(".", DIRECTORY_SEPARATOR, $blade) . ".php"]));
        $retHtml = ob_get_contents();
        ob_end_clean();

        return $retHtml;
    }
}
