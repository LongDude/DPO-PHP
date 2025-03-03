ALTER database "lab2-db" SET TIMEZONE to 'Europe/Moscow';

create table if not exists feedback (
    id serial primary key,
    user_name varchar(100) NOT NULL,
    user_surname varchar(100),
    user_patronymic varchar(100),
    email varchar(100) NOT NULL UNIQUE,
    phone varchar(100) NOT NULL UNIQUE,
    comment varchar(256) NOT NULL,
    created_at timestamp NOT NULL DEFAULT NOW(),
    updated_at timestamp NOT NULL DEFAULT NOW()
);

CREATE OR REPLACE FUNCTION trigger_set_timestamp()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = NOW();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER set_timestamp
BEFORE UPDATE ON feedback
FOR EACH ROW
EXECUTE PROCEDURE trigger_set_timestamp();
