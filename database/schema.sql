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

create table pets
(
    pet_id        INTEGER not null
        constraint pets_pk
            primary key autoincrement,
    name          text    not null,
    species       text    not null,
    breed         text    not null,
    color                 not null,
    photo_url     text,
    status        text    not null,
    description   text    not null,
    date_reported date    not null,
    user_id       int
        constraint pets_users_user_id_fk
            references users,
    constraint check_name
        check (`status` in ('lost', 'found'))
);

create unique index pets_pet_id_uindex
    on pets (pet_id);

create index pets_user_id_index
    on pets (user_id);

create table sightings
(
    sighting_id INTEGER                            not null
        constraint sightings_pk
            primary key autoincrement,
    pet_id      int                                not null
        constraint sightings_pets_pet_id_fk
            references pets,
    user_id     int                                not null
        constraint sightings_users_user_id_fk
            references users,
    comment     text                               not null,
    latitude    double                             not null,
    longitude   double                             not null,
    timestamp   datetime default current_timestamp not null
);

create unique index sightings_sighting_id_uindex
    on sightings (sighting_id);

