create table users
(
    user_id       INTEGER             not null
        constraint users_pk
            primary key autoincrement,
    username      TEXT                not null
        constraint users_pk_2
            unique,
    password_hash TEXT                not null,
    role          TEXT default 'user' not null,
    constraint check_name
        check (`role` in ('user', 'admin', 'owner'))
);

