# MSSQL(Microsoft SQL Server)のインストールとPHPからPDOでアクセスするところまで

主に私の備忘録として残しておく

## 1. はじめに

[SQL Server on Linux のインストール ガイド](https://learn.microsoft.com/ja-jp/sql/linux/sql-server-linux-setup?view=sql-server-ver16)  
[クイック スタート:Red Hat に SQL Server をインストールし、データベースを作成する](https://learn.microsoft.com/ja-jp/sql/linux/quickstart-install-connect-red-hat?view=sql-server-ver16&tabs=rhel8)

上記を参考にRHEL9系にMSSQLをインストールする

## 2. 前提として

2023-10-22現在、クイックスタートのRed Hat 9(プレビュー)でインストールしようと思ったが、180日のプレビュー版なのが個人的にはどうもイヤだったので、Red Hat 8用のrpmをダウンロードしてインストールする

### 2-1. SELINUX

検証用なので、SELINUXはオフにする  
セットアップや、ログイン時に失敗するので  
オフにしなくても、きちんと設定すればいけるとは思うけど…

## 3. インストール

以下のコマンドを実行すればOK.

```console
# dnf install compat-openssl11 https://packages.microsoft.com/rhel/8/mssql-server-2022/Packages/m/mssql-server-16.0.4085.2-1.x86_64.rpm https://packages.microsoft.com/rhel/8/prod/Packages/m/mssql-tools18-18.2.1.1-1.x86_64.rpm https://packages.microsoft.com/rhel/8/prod/Packages/m/msodbcsql18-18.3.2.1-1.x86_64.rpm

The license terms for this product can be downloaded from
https://aka.ms/odbc18eula and found in
/usr/share/doc/msodbcsql18/LICENSE.txt . By entering 'YES',
you indicate that you accept the license terms.

Do you accept the license terms? (Enter YES or NO)
YES
  インストール中   : msodbcsql18-18.3.2.1-1.x86_64                          3/4 
  scriptletの実行中: msodbcsql18-18.3.2.1-1.x86_64                          3/4 
odbcinst: Driver installed. Usage count increased to 1. 
    Target directory is /etc

  scriptletの実行中: mssql-tools18-18.2.1.1-1.x86_64                        4/4 
The license terms for this product can be downloaded from
http://go.microsoft.com/fwlink/?LinkId=746949 and found in
/usr/share/doc/mssql-tools18/LICENSE.txt . By entering 'YES',
you indicate that you accept the license terms.

Do you accept the license terms? (Enter YES or NO)
YES
```

## 4. セットアップ

以下のコマンドを実行

```console
# /opt/mssql/bin/mssql-conf setup

SQL Server のエディションを選択します:
  1) Evaluation (無料、製品使用権なし、期限 180 日間)
  2) Developer (無料、製品使用権なし)
  3) Express (無料)
  4) Web (有料)
  5) Standard (有料)
  6) Enterprise (有料) - CPU core utilization restricted to 20 physical/40 hyperthreaded
  7) Enterprise Core (有料) - CPU core utilization up to Operating System Maximum
  8) 小売販売チャネルを介してライセンスを購入し、入力するプロダクト キーを持っています。
  9) Standard (Billed through Azure) - Use pay-as-you-go billing through Azure.
 10) Enterprise Core (Billed through Azure) - Use pay-as-you-go billing through Azure.

エディションの詳細については、以下を参照してください
https://go.microsoft.com/fwlink/?LinkId=2109348&clcid=0x411

このソフトウェアの有料エディションを使用するには、個別のライセンスを以下から取得する必要があります
Microsoft ボリューム ライセンス プログラム。
有料エディションを選択することは、
このソフトウェアをインストールおよび実行するための適切な数のライセンスがあることを確認していることになります。
By choosing an edition billed Pay-As-You-Go through Azure, you are verifying 
that the server and SQL Server will be connected to Azure by installing the 
management agent and Azure extension for SQL Server.

エディションを入力してください(1-10): 3
この製品のライセンス条項は
/usr/share/doc/mssql-server or downloaded from: https://aka.ms/useterms

プライバシーに関する声明は、次の場所で確認できます:
https://go.microsoft.com/fwlink/?LinkId=853010&clcid=0x411

ライセンス条項に同意しますか? [Yes/No]: Yes


SQL Server の言語の選択:
(1) English
(2) Deutsch
(3) Español
(4) Français
(5) Italiano
(6) 日本語
(7) 한국어
(8) Português
(9) Руѝѝкий
(10) 中文 – 简体
(11) 中文 （繝体）
オプション 1-11 を入力: 6
SQL Server システム管理者パスワードを入力してください: {パスワード}
SQL Server システム管理者パスワードを確認入力してください: {パスワード確認}
SQL Server を構成しています...
セットアップは正常に完了しました。SQL Server を起動しています。
```

## 5. 接続確認

```console
# /opt/mssql-tools18/bin/sqlcmd -No -S localhost sa
Password: {セットアップ時に設定したパスワード}
1>
```

1>のプロンプトが出れば接続できた

## 6. PDOを使用しての利用

### 6-1. PDO_SQLSRVのインストール

Remiリポジトリ等で入れたPHPであれば、php-sqlsrvパッケージを入れればよい  
ここでは、  
[Microsoft SQL Server 用 Drivers for PHP をダウンロードする](https://learn.microsoft.com/ja-jp/sql/connect/php/download-drivers-php-sql-server?view=sql-server-ver16)  
をもとにして、インストールする  

[5.11.0 for PHP Driver for SQL Server](https://github.com/Microsoft/msphpsql/releases/v5.11.0)  
によると、peclコマンドで入れるようなので、  

```console
# dnf install php-pear
# pecl install sqlsrv-5.11.0
# pecl install pdo_sqlsrv-5.11.0
```

しようとしたが、2番目のpeclコマンドの途中でエラーに
PHPはphpパッケージとphp-develが必要なので、インスコしてからコマンドを実行したが、sql.hのヘッダファイルが無いとのこと  
unixODBC-develパッケージが必要だが、Miracle Linux 9には入っておらず…  
よって、  
[pkgs.org](https://pkgs.org)  
でunixODBC-develを検索して、CentOS Stream 9用のパッケージをインストール  
[https://centos.pkgs.org/9-stream/centos-crb-x86_64/unixODBC-devel-2.3.9-4.el9.x86_64.rpm.html](https://centos.pkgs.org/9-stream/centos-crb-x86_64/unixODBC-devel-2.3.9-4.el9.x86_64.rpm.html)  
(上記リンク先にはcaptchaがかかっていたので、具体的なパッケージのURLは出さないでおきます)

これで、

```console
# pecl install sqlsrv-5.11.0
# pecl install pdo_sqlsrv-5.11.0
```

どちらもOK.だったので、`/etc/php.d/20-sqlsrv.ini`、`/etc/php.d/30-pdo_sqlsrv.ini`を追加

```console
# vi /etc/php.d/20-sqlsrv.ini
extension=sqlsrv
# vi /etc/php.d/30-pdo_sqlsrv.ini
extension=pdo_sqlsrv
```

として、

```console
# php -i | grep -i pdo
/etc/php.d/20-pdo.ini,
/etc/php.d/30-pdo_sqlite.ini,
/etc/php.d/30-pdo_sqlsrv.ini,
PDO
PDO support => enabled
PDO drivers => sqlite, sqlsrv
pdo_sqlite
PDO Driver for SQLite 3.x => enabled
pdo_sqlsrv
pdo_sqlsrv support => enabled
pdo_sqlsrv.client_buffer_max_kb_size => 10240 => 10240
pdo_sqlsrv.log_severity => 0 => 0
pdo_sqlsrv.report_additional_errors => 1 => 1
pdo_sqlsrv.set_locale_info => 2 => 2
```

で、PDO_SQLSRVが認識された

### 6-2. 動作確認

以下のような簡単なスクリプトを書いて確認  
TLS接続してないので、DSNのTrustServerCertificate=1を付けないと怒られる  
何かデータベースに接続する場合は、  
Database={データベース名}  
をDSNに付与すればよい  
[PDO_SQLSRV DSN](https://www.php.net/manual/ja/ref.pdo-sqlsrv.connection.php)

```php
<?php
date_default_timezone_set("Asia/Tokyo");
mb_internal_encoding("UTF-8");

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
];
$db = new PDO("sqlsrv:Server=localhost,1433;TrustServerCertificate=1", "sa", "{パスワード}", $options);
$stmt = $db->prepare("SELECT @@version");
$stmt->execute();
$data = [];
while(($row = $stmt->fetch(PDO::FETCH_ASSOC)) != false){
    $data[] = $row;
}
$stmt->closeCursor();
print_r($data);
```

実行結果

```console
Array
(
    [0] => Array
        (
            [] => Microsoft SQL Server 2022 (RTM-CU9) (KB5030731) - 16.0.4085.2 (X64) 
	Sep 27 2023 12:05:43 
	Copyright (C) 2022 Microsoft Corporation
	Express Edition (64-bit) on Linux (MIRACLE LINUX 9.2 (Feige)) <X64>
        )

)
```