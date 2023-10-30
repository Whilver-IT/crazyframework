<?php
date_default_timezone_set("Asia/Tokyo");
mb_internal_encoding("UTF-8");

$options = [
	PDO::ATTR_CASE => PDO::CASE_LOWER,
	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
];
$db = new PDO("oci:dbname=//192.168.128.188:1521/CRAZYPDB;charset=AL32UTF8", "whilver", "db#elcaro", $options);

// テーブルがあるかどうか念の為確認
$stmt = $db->prepare("SELECT COUNT(*) AS cnt FROM user_tables WHERE table_name = :table_name");
$stmt->bindValue(":table_name", "TEST", PDO::PARAM_STR);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt->closeCursor();
if($row === false || $row['cnt']){
	echo "fetchに失敗したか、テーブルが既に存在するので処理を中断します";
	exit;
} 

// テーブル作成
$stmt = $db->prepare("CREATE TABLE test (id VARCHAR2(16) NOT NULL PRIMARY KEY, name VARCHAR2(4000))");
$stmt->execute();
$stmt->closeCursor();

// データ投入
$stmt = $db->prepare("INSERT INTO test (id, name) VALUES (:id, :name)");
$stmt->bindValue(":id", "foo", PDO::PARAM_STR);
$stmt->bindValue(":name", "フー", PDO::PARAM_STR);
$stmt->execute();
$stmt->closeCursor();

// 表示
printData($db);

// データ変更
$stmt = $db->prepare("UPDATE test SET name = :name WHERE id = :id");
$stmt->bindValue(":name", "バー", PDO::PARAM_STR);
$stmt->bindValue(":id", "foo", PDO::PARAM_STR);
$stmt->execute();
$stmt->closeCursor();

// 表示
printData($db);

// データ削除
$stmt = $db->prepare("DELETE FROM test WHERE id = :id");
$stmt->bindValue(":id", "foo", PDO::PARAM_STR);
$stmt->execute();
$stmt->closeCursor();

// 表示
printData($db);

// テーブル削除
$stmt = $db->prepare("DROP TABLE test");
$stmt->execute();
$stmt->closeCursor();

// 表示用メソッド
function printData(PDO $db){
	$stmt = $db->prepare("SELECT * FROM TEST ORDER BY id");
	$stmt->execute();
	$data = [];
	while(($row = $stmt->fetch(PDO::FETCH_ASSOC)) != false){
		$data[] = $row;
	}
	$stmt->closeCursor();
	if(php_sapi_name() == "cli"){
		print_r($data);
	} else {
		echo "<pre>" . print_r($data, true) . "</pre>";
	}
}
