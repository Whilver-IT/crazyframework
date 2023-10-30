<?php
/**
 * インデックス
 * publicディレクトリ配下にリソースがない場合はすべてここに来る
 */

$baseDir = realpath(__DIR__ . DIRECTORY_SEPARATOR . "..");

require_once(implode(DIRECTORY_SEPARATOR, [$baseDir, "bootstrap", "app.php"]));

$app = new \Bootstrap\App($baseDir);
unset($baseDir);

$app->send();