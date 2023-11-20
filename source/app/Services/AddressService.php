<?php

namespace App\Services;

use \App\Services\ServiceBase;
use \App\Repositoies\ZipinfoRepository;

/**
 * 住所関連クラス
 */
class AddressService extends ServiceBase {

    public function getZipInfo(string $zip = ""): array {
        return (new ZipInfoRepository($this->db))->getZipInfo(['where' => [ 'zip' => $zip ]]);
    }

    public function getZipInfoAll(string $zip = ""): array {
        return (new ZipInfoRepository($this->db))->getZipInfoAll(['where' => ['zip' => $zip]]);
    }

}
