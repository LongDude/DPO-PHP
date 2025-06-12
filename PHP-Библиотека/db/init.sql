-- CREATE DATABASE IF NOT EXISTS 'lab-4';
-- USE 'lab-4';

CREATE TABLE IF NOT EXISTS "users" (
    id BIGSERIAL PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    roles JSONB NOT NULL,
    CONSTRAINT unique_email UNIQUE (email)
);

CREATE TABLE IF NOT EXISTS books (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    author VARCHAR(100),
    cover_path TEXT,
    file_path TEXT,
    upload_date DATE NOT NULL DEFAULT CURRENT_DATE,
    uploader_id BIGINT NOT NULL,
    read_date DATE,
    allow_download BOOLEAN NOT NULL DEFAULT FALSE,
    CONSTRAINT fk_book_uploader FOREIGN KEY (uploader_id) REFERENCES "users"(id) ON DELETE CASCADE
);
