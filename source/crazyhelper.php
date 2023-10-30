<?php
/**
 * helperを記述
 * 最低限の処理をするような雑多なものをここに書く
 */

 if(!function_exists('is_nullorempty')){
    /**
     * isset、empty、is_nullどれも判定が微妙なので
     *
     * @param mixed $variable
     * @return boolean
     */
    function is_nullorempty(mixed $variable): bool {
        return ($variable === 0 || $variable === "0" || $variable === false) ? false : empty($variable);
    }
 }

if(!function_exists('my_debug')){
    /**
     * 変数情報出力
     *
     * @param mixed $variable
     * @param boolean $is_html
     * @param boolean $is_var_dump
     * @return void
     */
    function my_debug(mixed $variable, bool $is_html = false, bool $is_var_dump = false): void {
        if($is_html){
            echo "<pre>";
        }

        if($is_var_dump){
            var_dump($variable);
        } else {
            print_r($variable);
        }

        if($is_html){
            echo "</pre>";
        }
    }
}
