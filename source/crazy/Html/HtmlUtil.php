<?php

namespace Crazy\Html;

/**
 * HTMLユーティリティクラス
 */
class HtmlUtil {

    /**
     * コンストラクタ
     */
    private function __construct(){}

    /**
     * HTMLの始まりを記述
     *
     * @param array $attr
     * @return string
     */
    public static function startHtml(array $attr = []): string {
        $head = "";
        if(!is_nullorempty($attr['title']['value'])){
            $value = (is_nullorempty($attr['title']['is_html']) || $attr['title']['is_html'] === true) ? htmlspecialchars($attr['title']['value']) : $attr['title']['value'];
            $head .= "\n\t<title>" . $value . "</title>";
        }
        if(!is_nullorempty($attr['css'])){
            foreach($attr['css'] as $href){
                $href = is_nullorempty(trim($href)) ? "" : trim($href);
                $head .= "\n\t<link rel=\"stylesheet\" href=\"" . $href . "\">";
            }
        }
        if(!is_nullorempty($attr['script'])){
            foreach($attr['script'] as $script){
                if(!is_nullorempty($script['src'])){
                    $sync = !is_nullorempty($script['sync']) && ($script['sync'] == "async" || $script['sync'] == "defer") ? " " . $script['sync'] : "";
                    $head .= "\n\t<script src=\"" . $script['src'] . "\"" . $sync . "></script>";
                }
            }
        }

        return <<<HTML
<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">{$head}
</head>
HTML;
    }

    /**
     * HTMLの終わり部分の記述
     *
     * @param array $attr
     * @return string
     */
    public static function endHtml(array $attr = []): string {
        return <<<HTML
</html>
HTML;
    }

    /**
     * Csrf用トークン
     *
     * @return void
     */
    public static function csrf(bool $value_only = false): string {
        $csrf = base64_encode(random_bytes(256));
        $_SESSION['csrf'] = $csrf;
        return $value_only ? $csrf : "<input type=\"hidden\" name=\"_csrf\" value=\"" . $csrf . "\">";
    }

    /**
     * PATCHメソッド用
     *
     * @return string
     */
    public static function patch(): string {
        return "<input type=\"hidden\" name=\"_patch\" value=\"patch\">";
    }

}
