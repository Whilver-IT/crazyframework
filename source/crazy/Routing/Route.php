<?php

namespace Crazy\Routing;

use \Bootstrap\App;
use \Enum\RouteIndex;

/**
 * ルート処理
 * routes/web.phpの情報からルーティング一覧を作成する
 */
class Route {

    /**
     * ルーティング情報格納
     *
     * @var array
     */
    private static array $info = [];

    public static function init() : void {
        if(is_nullorempty(self::$info)){
            $urlInfo = include(implode(DIRECTORY_SEPARATOR, [App::$BASE_DIR, "routes", "web.php"]));
            foreach($urlInfo as $uInfo){
                foreach($uInfo['info'] as $info){
                    if(!is_nullorempty($info[self::getIndex(RouteIndex::NAME)])){
                        $pathKey = $uInfo['prefix'] . (is_nullorempty($uInfo['prefix']) ? "" : ".") . $info[self::getIndex(RouteIndex::NAME)];
                        self::$info['routes'][$pathKey] = (is_nullorempty($uInfo['prefix']) ? "" : "/" . $uInfo['prefix']) . $info[self::getIndex(RouteIndex::URI)];
                    }

                    if(!is_nullorempty(self::$info['current'])){
                        continue;
                    }
                    $path = (is_nullorempty($uInfo['prefix']) ? "" : "/" . $uInfo['prefix']) . $info[self::getIndex(RouteIndex::URI)];
                    $routeArr = explode("/", ltrim($path, "/"));
                    $requestArr = explode("/", str_replace("?" . $_SERVER['QUERY_STRING'], "", ltrim($_SERVER['REQUEST_URI'], "/")));

                    if (count($routeArr) != count($requestArr)){
                        continue;
                    }

                    $param = [];
                    for($arrIndex = 0; $arrIndex < count($routeArr); $arrIndex++){
                        if(substr($routeArr[$arrIndex], 0, 1) == ":"){
                            $param[substr($routeArr[$arrIndex], 1)] = $requestArr[$arrIndex];
                            continue;
                        }
                        if($routeArr[$arrIndex] != $requestArr[$arrIndex]){
                            break;
                        }
                    }

                    if($arrIndex == count($routeArr)){
                        self::$info['current'] = [
                            'name' => (is_nullorempty($uInfo['prefix']) ? "" : ($uInfo['prefix'] . ".")) . $info[self::getIndex(RouteIndex::NAME)],
                            'uri' => $path,
                            'method' => $info[self::getIndex(RouteIndex::METHOD)],
                            'middleware' => $info[self::getIndex(RouteIndex::MIDDLEWARE)],
                            'callable' => $info[self::getIndex(RouteIndex::CALLBACK)],
                            'islogincheck' => (isset($info[self::getIndex(RouteIndex::ISLOGIN)]) && $info[self::getIndex(RouteIndex::ISLOGIN)] === false) ? false : true,
                            'param' => $param,
                        ];
                    }
                }
            }

            //echo "<pre>" . print_r(self::$info, true) . "</pre>";
        }
    }

    /**
     * 配列のインデックスを取得
     *
     * @param object|integer $target
     * @return integer
     */
    public static function getIndex(object|int $target): int {
        return is_numeric($target) ? $target : $target->value();
    }

    /**
     * ルーティング情報一覧を返す
     *
     * @return array
     */
    public static function getInfo(): array {
        return self::$info;
    }

    /**
     * 名称に合った設定を返す
     *
     * @param string $name
     * @return string
     */
    public static function route(string $name): string {
        return self::$info['routes'][$name];
    }
}

Route::init();
