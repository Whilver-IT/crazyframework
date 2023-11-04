# PDO_OCIを使用してOracleを操作する

## 1. 目的

PHPからOracleを操作するのは面倒な印象なのでまとめておく

## 2. 前提条件

RHEL9系を最小構成でインストール  
本ドキュメント作成においては、[eurolinux](https://en.euro-linux.com/)にて行った  

尚、[Oracle XE 21cをインストールして最低限使うところまで](/Oracle-XE-21cインストール.md)  
でOracle XE 21cを入れたマシンとは別のマシンで行っている

## 3. Oracle Instant Client 21のインストール

PDO_OCIを入れるマシンは、Oracle XE 21cを入れたマシンとは別マシンであることは既出であるが、Oracle XE 21cを入れたマシンと同じバージョンのOracle Instant Clientとsqlplusをインストールする  
今回は21.3.0.0.0

```console
# dnf install https://yum.oracle.com/repo/OracleLinux/OL8/oracle/instantclient21/x86_64/getPackage/oracle-instantclient-basic-21.3.0.0.0-1.x86_64.rpm https://yum.oracle.com/repo/OracleLinux/OL8/oracle/instantclient21/x86_64/getPackage/oracle-instantclient-sqlplus-21.3.0.0.0-1.x86_64.rpm
```

## 4. RemiリポジトリからPHPをインストール

### 4-1. Remiリポジトリの追加

```console
# dnf install https://rpms.remirepo.net/enterprise/remi-release-9.rpm
```

### 4-2. PHPの必要なパッケージをインストール

```console
# dnf install php82-php-common php82-php php82-php-mbstring php82-php-pdo php82-php-oci8 php82-php-xml php82-php-zip
```

## 5. 接続確認

### 5-1. SQL*Plusで接続

rootユーザで一旦行う

```console
# export LD_LIBRARY_PATH=/usr/lib/oracle/21/client64/lib
# export NLS_LANG=Japanese_JAPAN.AL32UTF8
# sqlplus {ユーザ}/{パスワード}@//{ホスト名}/{DB名}
```

tnsnames.oraの場合

```console
# sqlplus {ユーザ}/{パスワード}@//{TNS接続名}
```

上記以外の場合

```console
# sqlplus {ユーザ}/{パスワード}@//{ホスト名}/{DB名}
```

で、接続できることを確認

### 5-2. httpで接続

apacheをインストールして、http経由でPHPが動くようにして、以下のようなスクリプトを流して確認

```php
<?php
date_default_timezone_set("Asia/Tokyo");
mb_internal_encoding("UTF-8");

$options = [
	PDO::ATTR_CASE => PDO::CASE_LOWER,
	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
];
$db = new PDO("oci:dbname=//{ホスト}:{ポート}/{DB名};charset=AL32UTF8", "{ユーザ名}", "{パスワード}", $options);

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
```
