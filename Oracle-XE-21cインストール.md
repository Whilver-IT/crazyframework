# Oracle XE 21cをインストールして最低限使うところまで

## 1. 目的

Oracle XE 21cをLinuxにインストールしてプラガブル・データベース、テーブルの作成、データの操作が行えるようにする

### 1-1. 参考サイト

[Oracle Database 21c Express Edition (XE) RPM Installation On Oracle Linux 9 << Sriram Sanka - My Experiences with Databases & More](https://ramoradba.com/2023/03/29/oracle-database-21c-express-edition-xe-rpm-installation-on-oracle-linux-9-2/)  
[Oracle Database 19c: Create Database 文で Databaseを作成してみてみた](https://qiita.com/shirok/items/a50dc0db7c89658c5162)

## 2. 環境

今回はOracle Linux 9.2を最小構成でVirtualBoxに固定ディスク40GB、メモリ4GBでインストール  
他は試してないが、他のRHEL9系のディストリビューション(almaとかrockyとか)でも多分大丈夫かと

## 3. インストール

2023-10-15現在RHEL9系のOracle XE 21cのrpmパッケージは無いようなので、OL8(Oracle Linex 8)のものをインストールする(またXEはel9のパッケージ無いのかな…少し調べてみたけど見つけられなかった…)  
OL8用のoracle-database-xe-21cのrpmだけだと、compat-openssl10が必要と言われて怒られるので、[pkgs.org](https://pkgs.org)からRHEL9用の[compat-openssl10](https://rhel.pkgs.org/9/raven-x86_64/compat-openssl10-1.0.2u-1.el9.x86_64.rpm.html)を合わせてダウンロードする

### 3-1. 必要なパッケージのダウンロードとインストール

```console
# mkdir /root/work
# cd /root/work
# curl -L -O https://pkgs.dyn.su/el9/base/x86_64/compat-openssl10-1.0.2u-1.el9.x86_64.rpm
# curl -L -O https://yum.oracle.com/repo/OracleLinux/OL8/appstream/x86_64/getPackage/oracle-database-preinstall-21c-1.0-1.el8.x86_64.rpm
# curl -L -O https://download.oracle.com/otn-pub/otn_software/db-express/oracle-database-xe-21c-1.0-1.ol8.x86_64.rpm
# dnf install *.rpm
```

## 4. インストール後の初期設定

3-1のインストール中に以下のメッセージが表示される  

```console
[INFO] Executing post installation scripts...
[INFO] Oracle home installed successfully and ready to be configured.
To configure Oracle Database XE, optionally modify the parameters in '/etc/sysconfig/oracle-xe-21c.conf' and then execute '/etc/init.d/oracle-xe-21c configure' as root.
```

`/etc/sysconfig/oracle-xe-21c.conf`でパラメータをセットして、rootユーザで`/etc/init.d/oracle-xe-21c configure`を実行しろと言われるので、`/etc/init.d/oracle-xe-21c configure`を実行する  
但しこのままだと、以下のエラーとなる

```console
[WARNING] [INS-08109] 状態'DBCreationOptions'で入力の検証中に予期せぬエラーが発生しました。
   原因: 使用可能な追加情報はありません。
   処置: Oracleサポート・サービスに連絡するか、ソフトウェア・マニュアルを参照してください。
   サマリー:
       - java.lang.NullPointerException
```

(細かく調べたわけではないですが)CV_ASSUME_DISTIDの環境変数をセットしないといけないようです  
値は何でもいいみたいです

```console
# export CV_ASSUME_DISTID=xxx
# /etc/init.d/oracle-xe-21c configure
```

とするか、

```console
# env CV_ASSUME_DISTID=xxx /etc/init.d/oracle-xe-21c configure
```

で、パスワードを入れて待つ

```console
# env CV_ASSUME_DISTID=xxx /etc/init.d/oracle-xe-21c configure
Specify a password to be used for database accounts. Oracle recommends that the password entered should be at least 8 characters in length, contain at least 1 uppercase character, 1 lower case character and 1 digit [0-9]. Note that the same password will be used for SYS, SYSTEM and PDBADMIN accounts: [パスワード]
Confirm the password: [パスワード確認]
Configuring Oracle Listener.
Listener configuration succeeded.
Configuring Oracle Database XE.
SYSユーザー・パスワードを入力してください: 
***************
SYSTEMユーザー・パスワードを入力してください: 
**************
PDBADMINユーザー・パスワードを入力してください: 
************* 
DB操作の準備
7%完了
データベース・ファイルのコピー中
29%完了
Oracleインスタンスの作成および起動中
30%完了
33%完了
37%完了
40%完了
43%完了
データベース作成の完了
47%完了
50%完了
プラガブル・データベースの作成
54%完了
71%完了
構成後アクションの実行
93%完了
カスタム・スクリプトを実行中
100%完了
データベースの作成が完了しました。詳細は、次の場所にあるログ・ファイルを参照してください:
/opt/oracle/cfgtoollogs/dbca/XE。
データベース情報:
グローバル・データベース名:XE
システム識別子(SID):XE
詳細はログ・ファイル"/opt/oracle/cfgtoollogs/dbca/XE/XE.log"を参照してください。

Connect to Oracle Database using one of the connect strings:
     Pluggable database: localhost.localdomain/XEPDB1
     Multitenant container database: localhost.localdomain
Use https://localhost:5500/em to access Oracle Enterprise Manager for Oracle Database XE
```

## 5. SQL*Plusで接続

### 5-1. 環境変数の設定

DBの作成くらいまでは、rootユーザで接続させたいのでrootの.bashrcに以下を設定

```bash
export ORACLE_HOME=/opt/oracle/product/21c/dbhomeXE
export NLS_LANG=JAPANESE_JAPAN.AL32UTF8
export PATH=$PATH:$ORACLE_HOME/bin #この行は臨機応変に対応してください
```

### 5-2. 接続

環境変数を反映(sourceコマンドやログイン、ログアウトなどして)後、SYSユーザで接続

```console
# source .bashrc
# sqlplus SYS/{4で設定したパスワード}@//localhost:1521/XE as sysdba
SQL*Plus: Release 21.0.0.0.0 - Production on 火 10月 17 23:00:36 2023
Version 21.3.0.0.0

Copyright (c) 1982, 2021, Oracle.  All rights reserved.



Oracle Database 21c Express Edition Release 21.0.0.0.0 - Production
Version 21.3.0.0.0
に接続されました。
```

接続を確認

## 6. プラガブル・データベース(Pluggable Database)

### 6-1. はじめに

XEではXEPDB1というデフォルトのプラガブル・データベースがあるので、それを利用する場合はこの項は読み飛ばしてください  
ここでは新規プラガブル・データベースを作成して、その上にユーザやテーブルを作成するようにします

### 6-2. 準備など

#### 6-2-1. 現在のPDBを確認

SYSでXEにログインして接続とPDBの状態を確認する

```console
# sqlplus SYS/{パスワード}@//localhost:1521/XE as sysdba
SQL> show con_name;

CON_NAME
------------------------------
CDB$ROOT
SQL> show pdbs;

    CON_ID CON_NAME                       OPEN MODE  RESTRICTED
---------- ------------------------------ ---------- ----------
         2 PDB$SEED                       READ ONLY  NO
         3 XEPDB1                         READ WRITE NO
```

#### 6-2-2. 元となるPDB$SEEDとデフォルトのPDBであるXEPDB1のファイル位置を確認

```console
SQL> alter session set container=PDB$SEED;

セッションが変更されました。

SQL> select file_name from dba_data_files;

FILE_NAME
--------------------------------------------------------------------------------
/opt/oracle/oradata/XE/pdbseed/system01.dbf
/opt/oracle/oradata/XE/pdbseed/sysaux01.dbf
/opt/oracle/oradata/XE/pdbseed/undotbs01.dbf

SQL> alter session set container=XEPDB1;

セッションが変更されました。

SQL> select file_name from dba_data_files;

FILE_NAME
--------------------------------------------------------------------------------
/opt/oracle/oradata/XE/XEPDB1/system01.dbf
/opt/oracle/oradata/XE/XEPDB1/sysaux01.dbf
/opt/oracle/oradata/XE/XEPDB1/undotbs01.dbf
/opt/oracle/oradata/XE/XEPDB1/users01.dbf
```

PDB$SEEDは/opt/oracle/oradata/XE/pdbseedディレクトリ、XEPDB1は/opt/oracle/oradata/XE/XEPDB1ディレクトリにファイルが有るので、  
今回はCRAZYPDBというPDBを/opt/oracle/oradata/XE/CRAZYPDBに作ることにする  
(各自の環境で「CRAZYPDB」の部分は好きなものに置き換えていただいても構いません。また、DB名とディレクトリ名を合わせる必要もない(はず)です)

### 6-3. CRAZYPDBの作成

#### 6-3-1. /opt/oracle/oradata/XE配下のディレクトリを確認

```console
# ls -l /opt/oracle/oradata/XE
合計 2715408
drwxr-x---. 2 oracle oinstall        104 10月 17 22:35 XEPDB1
-rw-r-----. 1 oracle oinstall   18726912 10月 18 00:10 control01.ctl
-rw-r-----. 1 oracle oinstall   18726912 10月 18 00:10 control02.ctl
drwxr-x---. 2 oracle oinstall        111 10月 17 22:25 pdbseed
-rw-r-----. 1 oracle oinstall  209715712 10月 17 22:35 redo01.log
-rw-r-----. 1 oracle oinstall  209715712 10月 17 22:35 redo02.log
-rw-r-----. 1 oracle oinstall  209715712 10月 18 00:10 redo03.log
-rw-r-----. 1 oracle oinstall  587210752 10月 18 00:10 sysaux01.dbf
-rw-r-----. 1 oracle oinstall 1394614272 10月 18 00:03 system01.dbf
-rw-r-----. 1 oracle oinstall  248520704 10月 17 22:29 temp01.dbf
-rw-r-----. 1 oracle oinstall  125837312 10月 18 00:10 undotbs01.dbf
-rw-r-----. 1 oracle oinstall    5251072 10月 17 22:35 users01.dbf
```

#### 6-3-2. /opt/oracle/oradataにディレクトリを作成

```console
# mkdir /opt/oracle/oradata/XE/CRAZYPDB
# chown oracle:oinstall /opt/oracle/oradata/XE/CRAZYPDB
# chmod 750 /opt/oracle/oradata/XE/CRAZYPDB
# ls -l /opt/oracle/oradata/XE
合計 2715408
drwxr-x---. 2 oracle oinstall          6 10月 18 00:24 CRAZYPDB
drwxr-x---. 2 oracle oinstall        104 10月 17 22:35 XEPDB1
-rw-r-----. 1 oracle oinstall   18726912 10月 18 00:26 control01.ctl
-rw-r-----. 1 oracle oinstall   18726912 10月 18 00:26 control02.ctl
drwxr-x---. 2 oracle oinstall        111 10月 17 22:25 pdbseed
-rw-r-----. 1 oracle oinstall  209715712 10月 17 22:35 redo01.log
-rw-r-----. 1 oracle oinstall  209715712 10月 17 22:35 redo02.log
-rw-r-----. 1 oracle oinstall  209715712 10月 18 00:25 redo03.log
-rw-r-----. 1 oracle oinstall  587210752 10月 18 00:21 sysaux01.dbf
-rw-r-----. 1 oracle oinstall 1394614272 10月 18 00:25 system01.dbf
-rw-r-----. 1 oracle oinstall  248520704 10月 17 22:29 temp01.dbf
-rw-r-----. 1 oracle oinstall  125837312 10月 18 00:25 undotbs01.dbf
-rw-r-----. 1 oracle oinstall    5251072 10月 17 22:35 users01.dbf
```

#### 6-3-2. CRAZYPDBの作成

SQL*PlusでSYSでログインして、CRAZYPDBというプラガブル・データベースを作成  
ADMIN USERはCRAZYPDBADMとする

```console
# sqlplus SYS/{パスワード}@//localhost:1521/XE as sysdba
SQL> CREATE PLUGGABLE DATABASE CRAZYPDB ADMIN USER CRAZYPDBADM IDENTIFIED BY {パスワード} FILE_NAME_CONVERT = ('/opt/oracle/oradata/XE/pdbseed', '/opt/oracle/oradata/XE/CRAZYPDB');

プラガブル・データベースが作成されました。
```

作成直後の状態はMOUNTEDなので、openして、stateを保存する

```console
SQL> show pdbs;

    CON_ID CON_NAME			              OPEN MODE  RESTRICTED
---------- ------------------------------ ---------- ----------
         2 PDB$SEED			              READ ONLY  NO
         3 XEPDB1			              READ WRITE NO
         4 CRAZYPDB			              MOUNTED

SQL> alter pluggable database crazypdb open;

プラガブル・データベースが変更されました。

SQL> show pdbs;

    CON_ID CON_NAME			              OPEN MODE  RESTRICTED
---------- ------------------------------ ---------- ----------
	     2 PDB$SEED			              READ ONLY  NO
	     3 XEPDB1			              READ WRITE NO
	     4 CRAZYPDB			              READ WRITE NO

SQL> alter pluggable database crazypdb save state;

プラガブル・データベースが変更されました。
```

一旦ログアウトして、作成したプラガブル・データベースへADMIN USERでログインする

```console
SQL> exit
Oracle Database 21c Express Edition Release 21.0.0.0.0 - Production
Version 21.3.0.0.0との接続が切断されました。
# sqlplus CRAZYPDBADM/{パスワード}@//localhost:1521/CRAZYPDB

SQL*Plus: Release 21.0.0.0.0 - Production on 木 10月 19 23:40:58 2023
Version 21.3.0.0.0

Copyright (c) 1982, 2021, Oracle.  All rights reserved.



Oracle Database 21c Express Edition Release 21.0.0.0.0 - Production
Version 21.3.0.0.0
に接続されました。
```

接続できることを確認しexitして、作成したCRAZYPDBのファイルを確認してみる

```console
SQL> exit
Oracle Database 21c Express Edition Release 21.0.0.0.0 - Production
Version 21.3.0.0.0との接続が切断されました。
# ls -l /opt/oracle/oradata/XE/CRAZYPDB
合計 737360
-rw-r-----. 1 oracle oinstall 356524032 10月 19 23:41 sysaux01.dbf
-rw-r-----. 1 oracle oinstall 293609472 10月 19 23:41 system01.dbf
-rw-r-----. 1 oracle oinstall  36708352 10月 19 23:26 temp012023-10-17_22-25-57-920-PM.dbf
-rw-r-----. 1 oracle oinstall 104865792 10月 19 23:41 undotbs01.dbf
```

### 6-4. ユーザとテーブルの作成

#### 6-4-1. 既存PDBのTABLESPACEの確認

デフォルトのXEPDB1のTABLESPACEの場所を確認

```console
# sqlplus SYS/{パスワード}@//localhost:1521/XEPDB1 as sysdba
SQL> select file_name, tablespace_name from dba_data_files;

FILE_NAME
--------------------------------------------------------------------------------
TABLESPACE_NAME
------------------------------
/opt/oracle/oradata/XE/users01.dbf
USERS

/opt/oracle/oradata/XE/undotbs01.dbf
UNDOTBS1

/opt/oracle/oradata/XE/system01.dbf
SYSTEM


FILE_NAME
--------------------------------------------------------------------------------
TABLESPACE_NAME
------------------------------
/opt/oracle/oradata/XE/sysaux01.dbf
SYSAUX
```

PDBのファイルと同じ場所に作られてるようなので、これと同じ要領で作成することにする

#### 6-4-2. TABLESPACE、TEMPORARY TABLESPACEの作成

以下の条件で、TABLESPACEとTEMPORARY TABLESPACEを作成する

| SPACE                | ファイル名                                           | 拡張方法       | 制限   |
| -------------------- | ---------------------------------------------------- | -------------- | ------ |
| TABLESPACE           | /opt/oracle/oradata/XE/CRAZYPDB/tablespace_crazy.dbf | 自動拡張 100MB | 無制限 |
| TEMPORARY TABLESPACE | /opt/oracle/oradata/XE/CRAZYPDB/temp_crazy.dbf       | 自動拡張 100MB | 無制限 |

SYSユーザでAS sysdbaで対象のプラガブル・データベースに接続して実行

```console
# sqlplus SYS/{パスワード}@//localhost:1521/CRAZYPDB as sysdba
SQL> CREATE TABLESPACE tablespace_crazy DATAFILE '/opt/oracle/oradata/XE/CRAZYPDB/tablespace_crazy.dbf' SIZE 100M AUTOEXTEND ON MAXSIZE UNLIMITED;

表領域が作成されました。

SQL> CREATE TEMPORARY TABLESPACE temp_crazy TEMPFILE '/opt/oracle/oradata/XE/CRAZYPDB/temp_crazy.dbf' SIZE 100M AUTOEXTEND ON MAXSIZE UNLIMITED;

表領域が作成されました。
```

#### 6-4-3. USERの作成

ADMIN USERとは別にテーブル作成、データ作成の可能なユーザを作成する  
このユーザを実際には使用することにする  
ユーザの権限は以下GRANTの通りとしておく

Oracleはユーザ作成後、デフォルトでは180日のパスワード有効期限なので、セキュリティ的にはよろしくないが無期限にしたい場合は、ALTER PROFILEで無期限にセットする

```console
# sqlplus SYS/{パスワード}@//localhost:1521/CRAZYPDB as sysdba
SQL> ALTER PROFILE DEFAULT LIMIT PASSWORD_LIFE_TIME UNLIMITED;

プロファイルが変更されました。

SQL> CREATE USER {ユーザ} IDENTIFIED BY {パスワード} DEFAULT TABLESPACE tablespace_crazy TEMPORARY TABLESPACE temp_crazy;            

ユーザーが作成されました。

SQL> SQL> GRANT CONNECT TO {ユーザ};

権限付与が成功しました。

SQL> GRANT RESOURCE TO {ユーザ};

権限付与が成功しました。

SQL> GRANT DBA TO {ユーザ};

権限付与が成功しました。
```

#### 6-4-4. TABLEの作成

6-4-3で作成したユーザで作成したプラガブル・データベースにアクセスして、テーブルの作成、データの作成等を行う

```console
# sqlplus {ユーザ}/{パスワード}@//localhost:1521/CRAZYPDB
SQL> CREATE TABLE TEST (id VARCHAR2(16) NOT NULL PRIMARY KEY, name VARCHAR2(2048));

表が作成されました。

SQL> INSERT INTO TEST (id, name) VALUES ('crazy', '狂人');

1行が作成されました。

SQL> SELECT * FROM TEST;

ID
----------------
NAME
--------------------------------------------------------------------------------
crazy
狂人

SQL> UPDATE TEST SET name = 'オリフレ' WHERE id = 'crazy';

1行が更新されました。

SQL> select * FROM TEST;

ID
----------------
NAME
--------------------------------------------------------------------------------
crazy
オリフレ

SQL> DELETE FROM TEST WHERE id = 'crazy';

1行が削除されました。

QL> SELECT COUNT(*) FROM TEST;

  COUNT(*)
----------
	     0

SQL> DROP TABLE TEST;

表が削除されました。
```

ここまでできれば完了です。お疲れ様でした。

## 7. PDO_OCIのインストール

[PDO_OCIをインストールしてOracleの操作.md](/PDO_OCIをインストールしてOracleの操作.md)  
をご覧ください