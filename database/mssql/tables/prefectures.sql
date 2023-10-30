-- 都道府県マスタ
create table prefectures (
      cd           varchar(2)  not null
    , name         varchar(6)  not null
    , suffix       varchar(2)
    , created_user varchar(32) not null
    , created_at   datetime2   not null
    , updated_user varchar(32) not null
    , updated_at   datetime2   not null
    , primary key(cd)
);
go

exec sys.sp_addextendedproperty
  @name=N'prefectures'
, @value=N'都道府県マスタ'
, @level0type = N'SCHEMA'
, @level0name = 'dbo'
, @level1type = 'TABLE'
, @level1name = 'prefectures';
go

exec sys.sp_addextendedproperty
  @name=N'prefectures.cd'
, @value=N'都道府県コード'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'prefectures'
, @level2type = N'Column'
, @level2name = 'cd';
go

exec sys.sp_addextendedproperty
  @name=N'prefectures.name'
, @value=N'都道府県名(末尾なし)'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'prefectures'
, @level2type = N'Column'
, @level2name = 'name';
go

exec sys.sp_addextendedproperty
  @name=N'prefectures.suffix'
, @value=N'都道府県末尾(都、府、県)'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'prefectures'
, @level2type = N'Column'
, @level2name = 'suffix';
go

exec sys.sp_addextendedproperty
  @name=N'prefectures.created_user'
, @value=N'作成日'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'prefectures'
, @level2type = N'Column'
, @level2name = 'created_user';
go

exec sys.sp_addextendedproperty
  @name=N'prefectures.created_at'
, @value=N'作成日'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'prefectures'
, @level2type = N'Column'
, @level2name = 'created_at';
go

exec sys.sp_addextendedproperty
  @name=N'prefectures.updated_user'
, @value=N'更新日'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'prefectures'
, @level2type = N'Column'
, @level2name = 'updated_user';
go

exec sys.sp_addextendedproperty
  @name=N'prefectures.updated_at'
, @value=N'作成日'
, @level0type = N'Schema'
, @level0name = 'dbo'
, @level1type = N'Table'
, @level1name = 'prefectures'
, @level2type = N'Column'
, @level2name = 'updated_at';
go
