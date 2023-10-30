<?php

use \Crazy\Html\HtmlUtil;
use \Crazy\Routing\Route;

$attr = [
    'title' => [
        'value' => "メニュー",
        'is_html' => true,
    ]
];

echo HtmlUtil::startHtml($attr);
?>
<body>
    メニュー画面です<br>
    <a href="/logout">ログアウト</a>
</body>
<?php
echo HtmlUtil::endHtml();
