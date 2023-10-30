<?php
use \Crazy\Html\HtmlUtil;

$attr = [
	'title' => [
		'value' => "ユーザ作成",
		'is_html' => true,
	],
	'css' => [
		"/css/common.css",
		"/css/createuser.css",
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
			'src' => '/js/createuser.js',
			'sync' => 'defer',
		],
	],
];

$dt = new DateTime();
$year = $dt->format('Y');
$byear_option = "<option value=\"\" selected>選択してください</option>";
for($i = $year; $i >= $year - 100; $i--){
	$byear_option .= "<option value=\"" . htmlspecialchars($i) . "\">" . htmlspecialchars($i) . "年</option>";
}

$bmon_option = "<option value=\"\" selected>選択してください</option>";
for($i = 1; $i <= 12; $i++){
	$bmon_option .= "<option value=\"" . htmlspecialchars($i) . "\">" . htmlspecialchars($i) . "月</option>";
}

$bday_option = "<option value=\"\" selected>選択してください</option>";
for($i = 1; $i <= 31; $i++){
	$bday_option .= "<option value=\"" . htmlspecialchars($i) . "\">" . htmlspecialchars($i) . "日</option>";
}

echo HtmlUtil::startHtml($attr);
?>
<body>
	<div>
		<form>
			<?php echo HtmlUtil::csrf(); ?>
			<div>
				ID(必須):&ensp;<input type="text" id="id" name="id" maxlength="32" placeholder="半角英数32文字以内で入力してください">
			</div>
			<div>
				パスワード(必須):&ensp;<input type="password" id="password" name="password" placeholder="半角英数記号で入力してください">
			</div>
			<div>
				パスワード確認(必須):&ensp;<input type="password" id="password_confirmation" name="password_confirmation" placeholder="パスワード確認">
			</div>
			<div>
				氏名(必須):&ensp;<input type="text" id="fname" name="fname" placeholder="姓">&ensp;<input type="text" id="name" name="name" placeholder="名">
			</div>
			<div>
				仮名:&ensp;<input type="text" id="fkana" name="fkana" placeholder="カナ(姓)">&ensp;<input type="text" id="nkana" name="nkana" placeholder="カナ(名)">
			</div>
			<div>
				誕生日:&ensp;<select id="byear" name="byear"><?php echo $byear_option; ?></select>&ensp;<select id="bmonth" name="bmonth"><?php echo $bmon_option; ?></select>&ensp;<select id="bday" name="bday"><?php echo $bday_option; ?></select>
			</div>
			<div>
				郵便番号:&ensp;<input type="text" id="zipcode" name="zipcode">&ensp;<input type="button" id="searchzip" name="searchzip" value="郵便番号から住所を検索">
			</div>
			<div>
				住所:&ensp;<input type="text" id="address" name="address" placeholder="例) 東京都渋谷区代々木1-1">
			</div>
			<div>
				電話番号1:&ensp;<input type="text" id="tel1" name="tel1">
			</div>
			<div>
				電話番号2:&ensp;<input type="text" id="tel2" name="tel2">
			</div>
			<div>
				メール1:&ensp;<input type="text" id="mail1" name="mail1">
			</div>
			<div>
				メール2:&ensp;<input type="text" id="mail2" name="mail2">
			</div>
			<div>
				<input type="button" id="regist" name="regist" value="登録">
			</div>
		</form>
		<a href="/">ログインページへ</a>
	</div>
</body>
<?php
echo HtmlUtil::endHtml();