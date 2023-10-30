/* 事業所郵便番号マスタ */
create table offices_zipinfo (
      id           int         not null comment 'ID'
    , zip          varchar(7)  not null comment '郵便番号'
    , pref_cd      varchar(2)  not null comment '都道府県コード'
    , city_cd      varchar(3)  not null comment '市区町村コード'
    , city         text        not null comment '市区町村名'
    , street       text                 comment '街頭詳細'
    , other        text                 comment 'その他'
    , company_name text        not null comment '事業所名'
    , company_kana text        not null comment '事業所名カナ'
    , created_user varchar(32) not null comment '作成者'
    , created_at   timestamp   not null comment '作成日'
    , updated_user varchar(32) not null comment '更新者'
    , updated_at   timestamp   not null comment '更新日'
    , primary key(id)
    , index (zip)
) comment '事業所用郵便番号マスタ';
