-- Valuer.si — Database setup
-- Run this script once to create the schema.

CREATE DATABASE IF NOT EXISTS valuer_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE valuer_db;

CREATE TABLE IF NOT EXISTS users (
    id       INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    ime      VARCHAR(100)    NOT NULL,
    priimek  VARCHAR(100)    NOT NULL,
    email    VARCHAR(255)    NOT NULL UNIQUE,
    geslo    VARCHAR(255)    NOT NULL,
    created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS valuations (
    id                INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    user_id           INT UNSIGNED  NOT NULL,
    naziv_narocnika   VARCHAR(255)  NOT NULL,
    naslov_narocnika  VARCHAR(255)  NOT NULL,
    namen_cenitve     ENUM(
        'zavarovano posojanje',
        'sodni postopek',
        'stečajni postopek',
        'računovodsko poročanje',
        'davčni postopek',
        'poslovna odločitev naročnika'
    ) NOT NULL,
    podlaga_vrednosti ENUM(
        'tržna vrednost',
        'likvidacijska vrednost',
        'tržna najemnina',
        'pravična vrednost'
    ) NOT NULL,
    premisa_vrednosti ENUM(
        'sedanja ali obstoječa uporaba',
        'najgospodarnejša uporaba',
        'redna likvidacija'
    ) NOT NULL,
    prvi_ogled        DATETIME      NOT NULL,
    created_at        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_valuations_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
