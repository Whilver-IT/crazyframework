-- ユーザマスタ
create table users (
      id           varchar(32) not null
    , password     text        not null
    , fname        text        not null
    , name         text        not null
    , fkana        text
    , nkana        text
    , byear        varchar(16)
    , bmonth       varchar(16)
    , bday         varchar(16)
    , zipcode      varchar(32)
    , address      text
    , tel1         varchar(32)
    , tel2         varchar(32)
    , mail1        text
    , mail2        text
    , token        text        not null
    , status       varchar(1)  not null
    , delflg       varchar(1)  not null
    , masterflg    varchar(1)  not null
    , created_user varchar(32) not null
    , created_at   datetime2   not null
    , updated_user varchar(32) not null
    , updated_at   datetime2   not null
    , primary key(id)
);
go

exec sys.sp_addextendedproperty
  @name=N'users'
, @value=N'ユーザマスタ'
, @level0type = N'SCHEMA'
, @level0name = 'dbo'
, @level1type = 'TABLE'
, @level1name = 'users';
go

exec sys.sp_addextendedproperty
  @name=N'users.id'
, @value=N'ID'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'users'
, @level2type = N'Column'
, @level2name = 'id';
go

exec sys.sp_addextendedproperty
  @name=N'users.fname'
, @value=N'苗字'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'users'
, @level2type = N'Column'
, @level2name = 'fname';
go

exec sys.sp_addextendedproperty
  @name=N'users.name'
, @value=N'名前'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'users'
, @level2type = N'Column'
, @level2name = 'name';
go

exec sys.sp_addextendedproperty
  @name=N'users.fkana'
, @value=N'仮名(姓)'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'users'
, @level2type = N'Column'
, @level2name = 'fkana';
go

exec sys.sp_addextendedproperty
  @name=N'users.nkana'
, @value=N'仮名(名)'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'users'
, @level2type = N'Column'
, @level2name = 'nkana';
go

exec sys.sp_addextendedproperty
  @name=N'users.byear'
, @value=N'誕生日(年)'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'users'
, @level2type = N'Column'
, @level2name = 'byear';
go

exec sys.sp_addextendedproperty
  @name=N'users.bmonth'
, @value=N'誕生日(月)'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'users'
, @level2type = N'Column'
, @level2name = 'bmonth';
go

exec sys.sp_addextendedproperty
  @name=N'users.bday'
, @value=N'誕生日(日)'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'users'
, @level2type = N'Column'
, @level2name = 'bday';
go

exec sys.sp_addextendedproperty
  @name=N'users.zipcode'
, @value=N'郵便番号'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'users'
, @level2type = N'Column'
, @level2name = 'zipcode';
go

exec sys.sp_addextendedproperty
  @name=N'users.address'
, @value=N'住所'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'users'
, @level2type = N'Column'
, @level2name = 'address';
go

exec sys.sp_addextendedproperty
  @name=N'users.tel1'
, @value=N'電話番号1'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'users'
, @level2type = N'Column'
, @level2name = 'tel1';
go

exec sys.sp_addextendedproperty
  @name=N'users.tel2'
, @value=N'電話番号2'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'users'
, @level2type = N'Column'
, @level2name = 'tel2';
go

exec sys.sp_addextendedproperty
  @name=N'users.mail1'
, @value=N'メール1'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'users'
, @level2type = N'Column'
, @level2name = 'mail1';
go

exec sys.sp_addextendedproperty
  @name=N'users.mail2'
, @value=N'メール2'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'users'
, @level2type = N'Column'
, @level2name = 'mail2';
go

exec sys.sp_addextendedproperty
  @name=N'users.token'
, @value=N'新規作成認証用トークン'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'users'
, @level2type = N'Column'
, @level2name = 'token';
go

exec sys.sp_addextendedproperty
  @name=N'users.status'
, @value=N'ステータス 認証前:0 認証済:1'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'users'
, @level2type = N'Column'
, @level2name = 'status';
go

exec sys.sp_addextendedproperty
  @name=N'users.delflg'
, @value=N'削除フラグ 通常:0 削除:1'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'users'
, @level2type = N'Column'
, @level2name = 'delflg';
go

exec sys.sp_addextendedproperty
  @name=N'users.masterflg'
, @value=N'権限フラグ 通常:0 管理者:1'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'users'
, @level2type = N'Column'
, @level2name = 'masterflg';
go

exec sys.sp_addextendedproperty
  @name=N'users.created_user'
, @value=N'作成者'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'users'
, @level2type = N'Column'
, @level2name = 'created_user';
go

exec sys.sp_addextendedproperty
  @name=N'users.created_at'
, @value=N'作成日'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'users'
, @level2type = N'Column'
, @level2name = 'created_at';
go

exec sys.sp_addextendedproperty
  @name=N'users.updated_user'
, @value=N'更新者'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'users'
, @level2type = N'Column'
, @level2name = 'updated_user';
go

exec sys.sp_addextendedproperty
  @name=N'users.updated_at'
, @value=N'更新日'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'users'
, @level2type = N'Column'
, @level2name = 'updated_at';
go
