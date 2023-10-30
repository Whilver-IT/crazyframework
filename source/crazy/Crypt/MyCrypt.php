<?php

namespace Crazy\Crypt;

use \Crazy\Config\Environment;
use ReflectionMethod;
use Throwable;

/**
 * 暗号化、復号クラス
 */
class MyCrypt {

	/**
	 * コンストラクタ
	 */
	private function __construct(){}

	/**
	 * 暗号化クラス初期設定
	 *
	 * @return void
	 */
	private static function init(): void {
		if(isset($_SERVER['argc'])){
			require_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "bootstrap", "app.php"]));
			require_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "Config", "Environment.php"]));
			Environment::init();
		}
	}

	/**
	 * 暗号化
	 *
	 * @param string $plain
	 * @param string $cipher
	 * @return string
	 */
	public static function encrypt(string $plain, string $cipher = "aes-256-cbc"): string {
		try {
			if(is_nullorempty($plain)){
				return "";
			}
			self::init();
			$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
			$base64_iv = base64_encode($iv);
			echo $base64_iv . "\n";
			$pad_len = strlen($base64_iv) - strlen(rtrim($base64_iv, "="));
			$encrypt_string = $pad_len . rtrim($base64_iv, "=") . openssl_encrypt($plain, $cipher, base64_decode(Environment::get("APP_KEY", "")), 0, $iv);
		} catch(Throwable $th){
			// ログ
		}

		return is_nullorempty($encrypt_string) ? "" : $encrypt_string;
	}

	/**
	 * 復号
	 *
	 * @param string $encrypt_string
	 * @param string $cipher
	 * @return string
	 */
	public static function decrypt(string $encrypt_string, string $cipher = "aes-256-cbc"): string {
		try {
			if(is_nullorempty($encrypt_string)){
				return "";
			}
			self::init();
			$iv_len = openssl_cipher_iv_length($cipher);
			$pad_len = substr($encrypt_string, 0, 1);
			if(!is_numeric($pad_len)){
				throw new Throwable("ivのパディング長が数値ではありません。(暗号化文字列: " . $encrypt_string . ")");
			}
			$base64_iv = "";
			if($pad_len > 2){
				throw new Throwable("ivのパディング長が2より大きな値です。(暗号化文字列：" . $encrypt_string . ")");
			} else {
				$base64_iv = substr($encrypt_string, 1, floor(($iv_len + 2) / 3) * 4 - $pad_len) . str_pad("", $pad_len, "=");
			}
			$decrypt_string = openssl_decrypt(
				substr($encrypt_string, 1 + strlen($base64_iv) - $pad_len),
				$cipher,  
				base64_decode(Environment::get("APP_KEY", "")),
				0,
				base64_decode($base64_iv));
		} catch(Throwable $th){
			// ログ
		}

		return is_nullorempty($decrypt_string) ? "" : $decrypt_string;
	}

	/**
	 * 暗号化キージェネレート
	 *
	 * @param string $cipher 暗号化モジュール
	 * @return void
	 */
	public static function generate(string $cipher = "aes-256-cbc"): void {
		if(isset($_SERVER['argc'])){
			self::init();
			$key = base64_encode(openssl_random_pseudo_bytes(openssl_cipher_key_length($cipher)));
			$method = new ReflectionMethod(Environment::class, "getEnvFile");
			$method->setAccessible(true);
			$file = $method->invoke(null);
			if(file_exists($file)){
				$contents = file_get_contents($file);
				preg_match("/^\\s*APP_KEY\\s*=(.*)$/m", $contents, $matches);
				$newContents = is_nullorempty($matches) ? "APP_KEY=" . $key . "\n" . ltrim($contents) : str_replace($matches[0], "APP_KEY=" . $key, $contents);
				file_put_contents($file, $newContents);
			} else {
				file_put_contents($file, "APP_KEY=" . $key . "\n");
			}
		}
	}
}
