<?php

namespace Crazy\Validator;

use \stdClass;

/**
 * バリデーションクラス
 * laravelのバリデーションを模倣して作ってみる
 */
class Validator {

    /**
     * バリデーション
     *
     * @param array $items
     * @param array $rules
     * @return array
     */
    public static function validate(array $items, array $rules): array {

        $validate_result = new stdClass();

        foreach($rules as $rules_key => $rules_values){
            $target_item = self::getItem($items, explode(".", $rules_key));

            $is_bail = false;
            $is_nullable = false;
            $is_confirmation = false;
            foreach($rules_values as $rule){
                $rule_and_option = explode(":", $rule);
                if($rule_and_option[0] == "bail"){
                    $is_bail = true;
                    continue;
                }
                switch($rule_and_option[0]){
                case "nullable":
                    if(is_nullorempty($target_item)){
                        if(!is_nullorempty($validate_result->{$rules_key})){
                            foreach($validate_result->{$rules_key} as $key => $result_rule){
                                if(is_nullorempty($result_rule['method']) || $result_rule['method'] != "required_unless"){
                                    unset($validate_result->{$rules_key}[$key]);
                                }
                            }
                            if(count($validate_result->{$rules_key}) == 0){
                                unset($validate_result->{$rules_key});
                            }
                        }
                        $is_nullable = true;
                    }
                    break;
                case "required_unless":
                    self::{$rule_and_option[0]}($target_item, $rules_key, $validate_result, is_nullorempty($rule_and_option[1]) ? null : $rule_and_option[1], $items);
                    break;
                case "confirmed":
                    $is_confirmation = true;
                    break;
                default:
                    if($is_nullable){
                        break 2;
                    }
                    self::{$rule_and_option[0]}($target_item, $rules_key, $validate_result, is_nullorempty($rule_and_option[1]) ? null : $rule_and_option[1]);
                    break;
                }
                if($is_bail){
                    // bail指定前にすでにエラーがあった場合も続行しない
                    // エラーの有無によらずbail指定後のチェック時にエラーがあった場合だけ止めたい場合は考慮が必要
                    // そんなケースがあるか分からないけど…
                    if(!is_nullorempty($validate_result->{$rules_key})){
                        break;
                    }
                }
            }

            // confirmation
            if(!$is_bail && $is_confirmation && is_nullorempty($validate_result->{$rules_key})){
                $confirmation = $rules_key . "_confirmation";
                if(isset($items[$confirmation])){
                    self::confirmation([$rules_key, $confirmation], $items, $validate_result);
                }
            }
        }

        return json_decode(json_encode($validate_result), true);
    }

    /**
     * 対象のデータを取得
     *
     * @param array $items
     * @param array $keys
     * @return array|string|null
     */
    public static function getItem(array $items, array $keys): array|string|null {
        $target_item = null;
        foreach($keys as $target_key){
            $target_item = isset($target_item[$target_key]) ? $target_item[$target_key] : $items[$target_key];
        }
        return $target_item;
    }

    /**
     * 一般的に入力可能文字かどうか
     *
     * @param string $value
     * @param string $key
     * @param stdClass $validate_result
     * @return void
     */
    private static function enterable(string $value, string $key, stdClass $validate_result):void {
        if(!is_nullorempty($value)){
            if(!preg_match("/^[\x20-\x7e]+$/", $value)){
                $validate_result->{$key}[] = __FUNCTION__;
            }
        }
    }

    /**
     * 入力必須
     *
     * @param string $value
     * @param string $key
     * @param stdClass $validate_result
     * @return void
     */
    private static function required(string $value, string $key, stdClass $validate_result): void  {
         if(is_nullorempty($value)){
            $validate_result->{$key}[] = __FUNCTION__;
         } elseif(preg_match("/[\x00-\x1f]/", $value, $match) == 1){
            $validate_result->{$key}[] = __FUNCTION__;
         }
    }

    /**
     * 値が含まれるかどうか
     *
     * @param string $value
     * @param string $key
     * @param stdClass $validate_result
     * @param string $options
     * @return void
     */
    private static function in(string $value, string $key, stdClass $validate_result, string $options): void {
        $values = explode(",", $options);
        if(!in_array($value, $values)){
            $validate_result->{$key} = __FUNCTION__;
        }
    }

    /**
     * 値が含まれないかどうか
     *
     * @param string $value
     * @param string $key
     * @param stdClass $validate_result
     * @param string $options
     * @return void
     */
    private static function not_in(string $value, string $key, stdClass $validate_result, string $options): void {
        $in = new stdClass();
        self::in($value, $key, $in, $options);
        if(is_nullorempty($in->{$key})){
            $validate_result->{$key} = __FUNCTION__;
        }
    }

    /**
     * 連動必須
     *
     * @param string $value
     * @param string $key
     * @param stdClass $validate_result
     * @param string $options
     * @param array $items
     * @return void
     */
    private static function required_unless(string $value, string $key, stdClass $validate_result, string $options, array $items): void {
        $values = explode(",", $options);
        $target_item = self::getItem($items, explode(".", $values[0]));
        if($target_item != (array_key_exists(1, $values) ? $values[1] : "") && is_nullorempty($items[$key])){
            $validate_result->{$key}[] = ['method' => __FUNCTION__, 'key' => $values[0]];
        }
    }

    /**
     * 整数かどうか
     *
     * @param string|integer $value
     * @param string $key
     * @param stdClass $validate_result
     * @return void
     */
    private static function integer(string|int $value, string $key, stdClass $validate_result): void {
        if(!preg_match("/^[0-9]+$/", $value)){
            $validate_result->{$key}[] = __FUNCTION__;
        }
    }

    /**
     * 長さが指定の長さか
     *
     * @param string|integer|array $value
     * @param string $key
     * @param stdClass $validate_result
     * @param string|integer $options
     * @return void
     */
    private static function size(string|int|array $value, string $key, stdClass $validate_result, string|int $options): void {
        $size = explode(",", $options);
        $is_err = false;
        if(is_array($value)){
            $is_err = count($value) == $size[0] ? $is_err : true;
        } else {
            if(is_nullorempty($size[1]) || $size[1] != "multi"){
                $is_err = strlen($value) == $size[0] ? $is_err : true;
            } else {
                $is_err = mb_strlen($value) == $size[0] ? $is_err : true;
            }
        }

        if($is_err){
            $validate_result->{$key}[] = __FUNCTION__;
        }
    }

    /**
     * 最大ライン
     *
     * @param string|integer|array $value
     * @param string $key
     * @param stdClass $validate_result
     * @param string|integer $options
     * @return void
     */
    private static function max(string|int|array $value, string $key, stdClass $validate_result, string|int $options): void {
        $max = explode(",", $options);
        $is_err = false;
        if(is_array($value)){
            $is_err = count($value) <= $max[0] ? $is_err : true;
        } else {
            if(is_nullorempty($max[1]) || $max[1] != "multi"){
                $is_err = strlen($value) <= $max[0] ? $is_err : true;
            } else {
                $is_err = mb_strlen($value) <= $max[0] ? $is_err : true;
            }
        }

        if($is_err){
            $validate_result->{$key}[] = __FUNCTION__;
        }
    }

    /**
     * 最低ライン
     *
     * @param string|integer|array $value
     * @param string $key
     * @param stdClass $validate_result
     * @param string|integer $options
     * @return void
     */
    private static function min(string|int|array $value, string $key, stdClass $validate_result, string|int $options): void {
        $max = explode(",", $options);
        $is_err = false;
        if(is_array($value)){
            $is_err = count($value) >= $max[0] ? $is_err : true;
        } else {
            if(is_nullorempty($max[1]) || $max[1] != "multi"){
                $is_err = strlen($value) >= $max[0] ? $is_err : true;
            } else {
                $is_err = strlen($value) >= $max[0] ? $is_err : true;
            }
        }

        if($is_err){
            $validate_result->{$key}[] = __FUNCTION__;
        }
    }

    /**
     * 日付かどうか
     *
     * @param string $value
     * @param [type] $key
     * @param stdClass $validate_result
     * @return void
     */
    private static function date(string $value, $key, stdClass $validate_result): void {
        $is_ok = false;
        $separators = ["/", "-"];
        foreach($separators as $separator){
            $date_value = explode($separator, $value);
            $divide_count = count($date_value);
            if($divide_count == 1){
                // ここは8桁固定にする
                if(strlen($date_value[0]) != 8){
                    continue;
                }
                $year = substr($date_value[0], 0, 4);
                $month = substr($date_value[0], 4, 2);
                $day = substr($date_value[0], 6, 2);
                $date_value = [$year, $month, $day];
            } elseif($divide_count != 3){
                break;
            }
            if(preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", implode("-", $date_value))){
                if(checkdate($date_value[1], $date_value[2], $date_value[0])){
                    $is_ok = true;
                    break;
                }
            }
        }

        if(!$is_ok){
            $validate_result->{$key}[] = __FUNCTION__;
        }
    }

    /**
     * 電話番号かどうか
     *
     * @param string $value
     * @param string $key
     * @param stdClass $validate_result
     * @return void
     */
    private static function tel(string $value, string $key, stdClass $validate_result): void {
        // 以下ハイフンを除いた形で処理する(ハイフンの存在は関係なくなるが…)
        $tel_value = str_replace("-", "", $value);

        // 国際電話とか衛星電話とかでなくて、普通の電話番号対応とするので、10桁か11桁
        $is_ok = true;
        $required = new stdClass();
        self::required($tel_value, $key, $required);
        $is_ok = is_nullorempty($required->{$key});

        if($is_ok){
            $numeric = new stdClass();
            self::integer($tel_value, $key, $numeric);
            $is_ok = is_nullorempty($required->{$key});
        }

        if($is_ok){
            $size_10 = new stdClass();
            self::size($tel_value, $key, $size_10, 10);
            if(!is_nullorempty($size_10->{$key})){
                $size_11 = new stdClass();
                self::size($tel_value, $key, $size_11, 11);
                $is_ok = is_nullorempty($size_11->{$key});
            }
        }
        if(!$is_ok){
            $validate_result->{$key}[] = __FUNCTION__;
        }
    }

    /**
     * 「確認」のチェック
     *
     * @param array $key
     * @param array $items
     * @param stdClass $validate_result
     * @return void
     */
    private static function confirmation(array $key, array $items, stdClass $validate_result): void {
        if($items[$key[0]] != $items[$key[1]]){
            $validate_result->{$key[1]}[] = __FUNCTION__;
        }
    }

    /**
     * メールアドレスチェック
     * そこまでRFC準拠にはしない
     *
     * @param string $value
     * @param string $key
     * @param stdClass $validate_result
     * @return void
     */
    private static function email(string $value, string $key, stdClass $validate_result): void  {

        // 使用可能な記号(以下に限定する)
        static $AVAILABLE_SYMBOLS = ["!", "#", "$", "%", "&", "'", "*", "+", "-", "/", "=", "?", "^", "_", "`", "{", "|", "}", "~", ".", ];

        // 空の場合はエラーにしない
        $required = new stdClass();
        self::required($value, $key, $required);
        if(!is_nullorempty($required->{$key})){
            return;
        }

        // 全体で254文字以下はエラー
        $maxLen = new stdClass();
        self::max($value, $key, $maxLen, 254);
        if(!is_nullorempty($maxLen->{$key})){
            $validate_result->{$key}[] = __FUNCTION__;
            return;
        }

        // 「@」で区切って2つでないならエラー
        $mail_part = explode("@", $value);
        if(count($mail_part) != 2){
            $validate_result->{$key}[] = __FUNCTION__;
            return;
        }
        list($account, $domain) = $mail_part;

        // アカウント文字数検証
        $account_len = new stdClass();
        self::max($account, $key, $account_len, 63);
        if(!is_nullorempty($account_len->{$key})){
            $validate_result->{$key}[] = __FUNCTION__;
            return;
        }

        // アカウントに意図した文字が入っているか確認
        $account_buff = str_replace($AVAILABLE_SYMBOLS, "", $account);
        $account_buff = preg_replace("/[0-9A-Za-z]/", "", $account_buff);
        if(!is_nullorempty($account_buff)){
            $validate_result->{$key}[] = __FUNCTION__;
            return;
        }

        // 先頭もしくは末尾に「.」がある場合はエラー
        if(substr($account, 0, 1) == "." || substr($account, -1, 1) == "."){
            $validate_result->{$key}[] = __FUNCTION__;
            return;
        }

        // ドットが2つ以上並んでいる場合はエラー
        if(str_replace("..", "", $account) != $account){
            $validate_result->{$key}[] = __FUNCTION__;
            return;
        }

        // 実在するドメインかどうか
        $domain_list = dns_get_record($domain, DNS_MX);
        if(!(is_array($domain_list) && count($domain_list) > 0)){
            $validate_result->{$key}[] = __FUNCTION__;
            return;
        }
    }

    /**
     * ユーザ指定のバリデーション情報から、バリデーションのルールを抜き出す
     *
     * @param array $validate_setting
     * @return array
     */
    public static function getValidate(array $validate_setting): array {

        $validate = [];
        foreach($validate_setting as $key => $settings){
            foreach($settings as $setting_key => $setting){
                switch($setting_key){
                case "min":
                case "max":
                    $validate[$key][] = $setting_key . ":" . (is_nullorempty($setting['length']) ? "" : $setting['length']);
                    break;
                case "required_unless":
                    foreach($setting['items'] as $item){
                        $validate[$key][] = $setting_key . ":" . $item;
                    }
                    break;
                default:
                    $validate[$key][] = $setting_key;
                    break;
                }
            }
        }

        return $validate;
    }

    /**
     * ユーザ指定のバリデーション情報とバリデーションの結果から、エラーメッセージを返す
     *
     * @param array $validate_setting
     * @param array $validate_result
     * @return array
     */
    public static function getErrMsg(array $validate_setting, array $validate_result): array {

        $err_msg = [];
        foreach ($validate_setting as $key => $settings){
            $is_general = array_key_exists($key, $validate_result);
            $is_confirmation = array_key_exists($key . "_confirmation", $validate_result);
            if(!$is_general && !$is_confirmation){
                continue;
            }
            foreach($settings as $setting_key => $setting){
                if($is_general){
                    foreach($validate_result[$key] as $validate_result_key => $err_item){
                        if(is_array($err_item)){
                            foreach($err_item as $err_info_key => $err_info_value){
                                if($setting_key == $err_info_value){
                                    $err_msg[$key] = $setting['err_msg'];
                                }
                            }
                        } else {
                            if($setting_key == $err_item){
                                $err_msg[$key] = $setting['err_msg'];
                                break;
                            }
                        }
                    }
                }
                if($is_confirmation){
                    // confirmedを複数設定しても最初のconfirmedを採用する(複数設定はおかしいはず)
                    $is_confirmation = false;
                    if(!is_nullorempty($validate_setting[$key]['confirmed']['err_msg'])){
                        $err_msg[$key] = $validate_setting[$key]['confirmed']['err_msg'];
                    }
                }
            }
        }

        return $err_msg;
    }
}
