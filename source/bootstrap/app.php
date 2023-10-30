<?php

namespace Bootstrap;

date_default_timezone_set("Asia/Tokyo");
mb_internal_encoding("UTF-8");

use \Crazy\Routing\Route;
use \Crazy\Session\Session;
use \Crazy\View\View;
use \App\Services\AuthService;
use \Exception;
use \Throwable;

/**
 * アプリケーションクラス
 * 迷ったらここのソースから見てみる
 */
class App {

	/**
	 * ベースディレクトリ
	 *
	 * @var string
	 */
	public static string $BASE_DIR;

	/**
	 * autoload時に無視するディレクトリ
	 */
	private const IGNORE_DIR = [
		"bootstrap",
		"config",
		"public",
		"resources",
		"routes",
		"vendor",
	];

	/**
	 * autoload時に先に読み込んでおくファイル
	 */
	private const BEFORE_REQUIRE = [
		["crazyhelper.php"],
		["vendor", "autoload.php"],
		["crazy", "Config", "Environment.php"],
		["app", "Services", "ServiceBase.php"],
		["app", "Http", "Controllers", "ControllerBase.php"],
	];

	/**
	 * コンストラクタ
	 */
	public function __construct(string $baseDir){
		self::$BASE_DIR = $baseDir;
		$this->autoload();
	}

	/**
	 * Dom送信処理
	 *
	 * @return void
	 */
	public function send() : void {
		try {
			$route_info = (new Route())->getInfo();
			
			//echo "<pre>" . print_r($route_info, true) . "</pre>";exit;

			if(is_nullorempty($route_info['current'] ?? null)){
				// ルーティングにないので404
				header("HTTP/1.1 404 Not Found");
			} else {
				Session::start();
				$err_msg = $this->methodCheck($route_info);
				if(!is_nullorempty($err_msg)){
					throw new Exception($err_msg);
				}

				// middlewareが指定されている場合はmiddlewareを実行
				$middlewares = [];
				if(!is_nullorempty($route_info['current']['middleware'] ?? null)){
					if(is_array($route_info['current']['middleware'])){
						$middlewares = $route_info['current']['middleware'];
					} else {
						$middlewares[] = $route_info['current']['middleware'];
					}
				}
				foreach($middlewares as $middleware){

				}

				// ログイン処理を実行(ログイン画面が呼ばれた)
				if($route_info['current']['islogincheck'] && !AuthService::check()){
					if($route_info['current']['uri'] != "/ajax/login"){
						echo View::make("login.index", []);
						return;
					}
				}

				if($route_info['current']['name'] == "top"){
					// TOPでここまできたのでログイン済なので、menuへ遷移
					header("Location: /menu");
				} elseif($route_info['current']['name'] == "logout"){
					// ログアウト時はセッションを消す
					Session::destroy();
					header("Location: /");
				} elseif(is_callable($route_info['current']['callable'])){
					echo $route_info['current']['callable']($route_info['current']['param']);
				} else {
					echo (new $route_info['current']['callable'][0]())->{$route_info['current']['callable'][1]}();
				}
			}
		} catch(Throwable $th){
			//echo $th->getMessage() . "<br>";
			echo json_encode([$th->getMessage()]);
		}
	}

	/**
	 * autoload
	 *
	 * @return void
	 */
	private function autoload() :void {

		try {

			// 先にrequireさせるものを読込
			foreach(self::BEFORE_REQUIRE as $before_require){
				array_unshift($before_require, self::$BASE_DIR);
				$before_require_file = implode(DIRECTORY_SEPARATOR, $before_require);
				if(file_exists($before_require_file)){
					require_once($before_require_file);
				}
			}

			$is_old_version = !(PHP_MAJOR_VERSION >= 8 && PHP_MINOR_VERSION >= 1);
			$dirs[] = self::$BASE_DIR;
			while(count($dirs)){
				$dir = array_shift($dirs);
				// ドットで始まるディレクトリを加味したい場合もあるかもしれないので敢えてscandirで取得しておく
				$resources = scandir($dir);
				foreach($resources as $resource){
					$target_resource = $dir . DIRECTORY_SEPARATOR . $resource;
					if(is_dir($target_resource)) {
						if(substr($resource, 0, 1) == "."){
							continue;
						}
						if(substr($target_resource, -3, 3) == "old"){
							// oldで終わるディレクトリはここでは加味しない
							continue;
						}
						$ignore_index = array_search($resource, self::IGNORE_DIR);
						if($ignore_index !== false && $target_resource == self::$BASE_DIR . DIRECTORY_SEPARATOR . self::IGNORE_DIR[$ignore_index]){
							continue;
						}
						$dirs[] = $target_resource;
					} elseif (is_file($target_resource)) {
						if(substr($resource, -4) == ".php"){
							$target_array = [$dir, $resource];
							if($is_old_version && file_exists(implode(DIRECTORY_SEPARATOR, [$dir, "old", $resource]))){
								array_splice($target_array, 1, 0, "old");
							}
							require_once(implode(DIRECTORY_SEPARATOR, $target_array));
						}
					}
				}
			}
		} catch(Throwable $th){
			my_debug($th, true);exit;
		}
	}

	/**
	 * 指定のメソッドと実際のメソッドをチェック
	 *
	 * @param array $route_info
	 * @return string
	 */
	private function methodCheck(array $route_info): string {
		// 指定のメソッドと実際のメソッドが異なっていたらエラー
		$err_msg = "";
		$is_post_check = false;
		if($_SERVER['REQUEST_METHOD'] == strtoupper($route_info['current']['method'])){
			$is_post_check = ($_SERVER['REQUEST_METHOD'] == "POST");
		} else {
			if($_SERVER['REQUEST_METHOD'] == "POST" && $route_info['current']['method'] == "patch"){
				$is_post_check = true;
			} else {
				$err_msg = "リクエストとルーティングのメソッドが異なっています。(\$_SERVER['REQUEST_METHOD']: " . $_SERVER['REQUEST_METHOD'] . " ルーティング： " . $route_info['current']['method'] . ")";
			}
		}

		if($is_post_check){
			if(is_nullorempty($route_info['current']['method'] ?? null)){
				$err_msg = "ルーティングのメソッドが設定されていません。(ルーティング名: " . $route_info['current']['name'] . ")";
			}
			if(is_nullorempty($_SESSION['csrf'] ?? null)
			|| is_nullorempty($_POST['_csrf'] ?? null)
			|| $_SESSION['csrf'] != $_POST['_csrf']){
				$err_msg = "クロスサイトリクエストフォージュリ対策のキーが一致しません";
			}
		}

		return $err_msg;
	}

}
