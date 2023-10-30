-- ユーザマスタ
create table users (
      id           varchar(32)              not null
    , password     text                     not null
    , fname        text                     not null
    , name         text                     not null
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
    , token        text                     not null
    , status       varchar(1)               not null
    , delflg       varchar(1)               not null
    , masterflg    varchar(1)               not null
    , created_user varchar(32)              not null
    , created_at   timestamp with time zone not null
    , updated_user varchar(32)              not null
    , updated_at   timestamp with time zone not null
    , primary key(id)
);

comment on table users is 'ユーザマスタ';
comment on column users.id is 'ID';
comment on column users.password is 'パスワード';
comment on column users.fname is '苗字';
comment on column users.name is '名前';
comment on column users.fkana is '仮名(姓)';
comment on column users.nkana is '仮名(名)';
comment on column users.byear is '誕生日(年)';
comment on column users.bmonth is '誕生日(月)';
comment on column users.bday is '誕生日(日)';
comment on column users.zipcode is '郵便番号';
comment on column users.address is '住所';
comment on column users.tel1 is '電話番号1';
comment on column users.tel2 is '電話番号2';
comment on column users.mail1 is 'メール1';
comment on column users.mail2 is 'メール2';
comment on column users.token is '新規作成認証用トークン';
comment on column users.status is 'ステータス 認証前:0 認証済:1';
comment on column users.delflg is '削除フラグ 通常:0 削除:1';
comment on column users.masterflg is '権限フラグ 通常:0 管理者:1';
comment on column users.created_user is '作成者';
comment on column users.created_at is '作成日';
comment on column users.updated_user is '更新者';
comment on column users.updated_at is '更新日';
