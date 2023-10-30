-- 郵便番号マスタ
create table zipinfo (
      id           int         not null
    , zip          varchar(7)  not null
    , pref_cd      varchar(2)  not null
    , city_cd      varchar(3)  not null
    , city         text        not null
    , city_kana    text        not null
    , street       text
    , street_kana  text
    , created_user varchar(32) not null
    , created_at   datetime2   not null
    , updated_user varchar(32) not null
    , updated_at   datetime2   not null
    , primary key(id)
);
go

exec sys.sp_addextendedproperty
  @name=N'zipinfo'
, @value=N'郵便番号マスタ'
, @level0type = N'SCHEMA'
, @level0name = 'dbo'
, @level1type = 'TABLE'
, @level1name = 'zipinfo';
go

exec sys.sp_addextendedproperty
  @name=N'zipinfo.id'
, @value=N'ID'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'zipinfo'
, @level2type = N'Column'
, @level2name = 'id';
go

exec sys.sp_addextendedproperty
  @name=N'zipinfo.zip'
, @value=N'郵便番号'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'zipinfo'
, @level2type = N'Column'
, @level2name = 'zip';
go

exec sys.sp_addextendedproperty
  @name=N'zipinfo.pref_cd'
, @value=N'都道府県コード'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'zipinfo'
, @level2type = N'Column'
, @level2name = 'pref_cd';
go

exec sys.sp_addextendedproperty
  @name=N'zipinfo.city_cd'
, @value=N'市区町村コード'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'zipinfo'
, @level2type = N'Column'
, @level2name = 'city_cd';
go

exec sys.sp_addextendedproperty
  @name=N'zipinfo.city'
, @value=N'市区町村名'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'zipinfo'
, @level2type = N'Column'
, @level2name = 'city';
go

exec sys.sp_addextendedproperty
  @name=N'zipinfo.city_kana'
, @value=N'市区町村名カナ'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'zipinfo'
, @level2type = N'Column'
, @level2name = 'city_kana';
go

exec sys.sp_addextendedproperty
  @name=N'zipinfo.street'
, @value=N'街頭詳細'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'zipinfo'
, @level2type = N'Column'
, @level2name = 'street';
go

exec sys.sp_addextendedproperty
  @name=N'zipinfo.street_kana'
, @value=N'街頭詳細カナ'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'zipinfo'
, @level2type = N'Column'
, @level2name = 'street_kana';
go

exec sys.sp_addextendedproperty
  @name=N'zipinfo.created_user'
, @value=N'作成者'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'zipinfo'
, @level2type = N'Column'
, @level2name = 'created_user';
go

exec sys.sp_addextendedproperty
  @name=N'zipinfo.created_at'
, @value=N'作成日'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'zipinfo'
, @level2type = N'Column'
, @level2name = 'created_at';
go

exec sys.sp_addextendedproperty
  @name=N'zipinfo.updated_user'
, @value=N'更新者'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'zipinfo'
, @level2type = N'Column'
, @level2name = 'updated_user';
go

exec sys.sp_addextendedproperty
  @name=N'zipinfo.updated_at'
, @value=N'更新日'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'zipinfo'
, @level2type = N'Column'
, @level2name = 'updated_at';
go

create index index_01 on zipinfo (zip);
go
