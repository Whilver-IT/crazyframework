<?php

namespace App\Http\Controllers;

use \Crazy\View\View;
use \App\Http\Controllers\ControllerBase;

/**
 * メニュー画面コントローラクラス
 */
class MenuController extends ControllerBase {

    /**
     * index
     * 
     * メニュー画面を表示
     *
     * @return string
     */
    public function index(): string {
        return View::make("menu.index", []);
    }
}
