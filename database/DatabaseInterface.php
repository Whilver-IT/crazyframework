<?php

/**
 * インストーラDatabaseクラス用インタフェース
 */
interface DatabaseInterface {

    /**
     * DSN取得
     *
     * @return string
     */
    public function getDsn(): string;

    /**
     * PDOのDSNデータベース名キーワード設定
     *
     * @return string
     */
    public function getDsnDatabaseKeyword(): string;

    /**
     * データベース存在チェック
     *
     * @return boolean
     */
    public function checkExistDatabase(): bool;

    /**
     * テーブル存在チェック
     *
     * @param string $table_name
     * @return boolean
     */
    public function checkExistsTable(string $table_name): bool;
}