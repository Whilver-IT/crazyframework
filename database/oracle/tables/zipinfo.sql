-- 郵便番号マスタ
create table zipinfo (
      id           int                      not null
    , zip          varchar2(7)              not null
    , pref_cd      varchar2(2)              not null
    , city_cd      varchar2(3)              not null
    , city         varchar2(4000)           not null
    , city_kana    varchar2(4000)           not null
    , street       varchar2(4000)
    , street_kana  varchar2(4000)
    , created_user varchar2(32)             not null
    , created_at   timestamp with time zone not null
    , updated_user varchar2(32)             not null
    , updated_at   timestamp with time zone not null
    , primary key(id)
);

comment on table zipinfo is '郵便番号マスタ';
comment on column zipinfo.id is 'ID';
comment on column zipinfo.zip is '郵便番号';
comment on column zipinfo.pref_cd is '都道府県コード';
comment on column zipinfo.city_cd is '市区町村コード';
comment on column zipinfo.city is '市区町村名';
comment on column zipinfo.city_kana is '市区町村名カナ';
comment on column zipinfo.street is '街頭詳細';
comment on column zipinfo.street_kana is '街頭詳細カナ';
comment on column zipinfo.created_user is '作成者';
comment on column zipinfo.created_at is '作成日';
comment on column zipinfo.updated_user is '更新者';
comment on column zipinfo.updated_at is '更新日';

create index zipinfo_idx1 on zipinfo (zip);
