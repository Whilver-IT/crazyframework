-- 事業所郵便番号マスタ
create table offices_zipinfo (
      id           int                      not null
    , zip          varchar(7)               not null
    , pref_cd      varchar(2)               not null
    , city_cd      varchar(3)               not null
    , city         text                     not null
    , street       text
    , other        text
    , company_name text                     not null
    , company_kana text                     not null
    , created_user varchar(32)              not null
    , created_at   timestamp with time zone not null
    , updated_user varchar(32)              not null
    , updated_at   timestamp with time zone not null
    , primary key(id)
);

comment on table offices_zipinfo is '事業所用郵便番号マスタ';
comment on column offices_zipinfo.id is 'ID';
comment on column offices_zipinfo.zip is '郵便番号';
comment on column offices_zipinfo.pref_cd is '都道府県コード';
comment on column offices_zipinfo.city_cd is '市区町村コード';
comment on column offices_zipinfo.city is '市区町村名';
comment on column offices_zipinfo.street is '街頭詳細';
comment on column offices_zipinfo.other is 'その他';
comment on column offices_zipinfo.company_name is '事業所名';
comment on column offices_zipinfo.company_kana is '事業所名カナ';
comment on column offices_zipinfo.created_user is '作成者';
comment on column offices_zipinfo.created_at is '作成日';
comment on column offices_zipinfo.updated_user is '更新者';
comment on column offices_zipinfo.updated_at is '更新日';

create index on offices_zipinfo(zip);
