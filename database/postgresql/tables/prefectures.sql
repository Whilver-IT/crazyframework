-- 都道府県マスタ
create table prefectures (
      cd           varchar(2)               not null
    , name         varchar(3)               not null
    , suffix       varchar(1)
    , created_user varchar(32)              not null
    , created_at   timestamp with time zone not null
    , updated_user varchar(32)              not null
    , updated_at   timestamp with time zone not null
    , primary key(cd)
);

comment on table prefectures is '都道府県マスタ';
comment on column prefectures.cd is '都道府県コード';
comment on column prefectures.name is '都道府県名(末尾なし)';
comment on column prefectures.suffix is '都道府県末尾(都、府、県)';
comment on column prefectures.created_user is '作成者';
comment on column prefectures.created_at is '作成日';
comment on column prefectures.updated_user is '更新者';
comment on column prefectures.updated_at is '更新日';
