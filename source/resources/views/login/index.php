<?php

use \Crazy\Html\HtmlUtil;
use \Crazy\Routing\Route;

$attr = [
    'title' => [
        'value' => "ログイン",
        'is_html' => true,
    ],
    'css' => [
        '/css/common.css',
        '/css/login.css',
    ],
    'script' => [
        [
            'src' => 'https://unpkg.com/axios/dist/axios.min.js',
            'sync' => 'defer',
        ],
        [
            'src' => '/js/util/httputil.js',
            'sync' => 'defer',
        ],
        [
            'src' => '/js/util/eventutil.js',
            'sync' => 'defer',
        ],
        [
            'src' => '/js/login.js',
            'sync' => 'defer',
        ],
    ],
];

echo HtmlUtil::startHtml($attr);
?>
<body>
<div class="login_out_layer">
    <div class="login">
        <div class="logo">
            フレームワークテストシステムログイン
        </div>
        <form id="login_form">
            <?php echo HtmlUtil::csrf(); ?>
            <?php echo HtmlUtil::patch(); ?>
            <div>
                ID:&ensp;<input type="text" id="userid" name="userid" value="" placeholder="ログインID">
            </div>
            <div>
                パスワード:&ensp;<input type="password" id="password" name="password" value="" placeholder="パスワード">
            </div>
            <div>
                <input id="login" name="login" type="button" value="ログイン">
            </div>
            <div>
                <a href="<?php echo htmlspecialchars(Route::route("createuser")); ?>">ユーザアカウント作成ページへ</a>
            </div>
        </form>
    </div>
</div>
</body>
<?php
echo HtmlUtil::endHtml();
