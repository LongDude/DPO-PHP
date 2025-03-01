create table if not exists feedback (
    id serial primary key,
    fullname varchar(100),
    email varchar(100),
    phone varchar(100),
    comment varchar(256),
)
