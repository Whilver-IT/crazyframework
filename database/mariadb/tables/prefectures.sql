/* 都道府県マスタ */
create table prefectures (
      cd           varchar(2)  not null comment '都道府県コード'
    , name         varchar(3)  not null comment '都道府県名(末尾なし)'
    , suffix       varchar(1)           comment '都道府県末尾(都、府、県)'
    , created_user varchar(32) not null comment '作成者'
    , created_at   timestamp   not null comment '作成日'
    , updated_user varchar(32) not null comment '更新者'
    , updated_at   timestamp   not null comment '更新日'
    , primary key(cd)
) comment '都道府県マスタ';
