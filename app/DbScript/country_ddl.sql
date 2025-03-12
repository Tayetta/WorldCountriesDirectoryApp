-- создание БД --
DROP DATABASE IF EXISTS world_countries_db;
CREATE DATABASE world_countries_db;
-- переключение на данную БД --
USE world_countries_db;
-- создание таблицы стран мира -- 
CREATE TABLE countries_t (
    id INT NOT NULL AUTO_INCREMENT,
    title_short_f VARCHAR(200) NOT NULL,
    title_full_f VARCHAR(200) NOT NULL,
    letter_2_code_f CHAR(2) NOT NULL,
    letter_3_code_f CHAR(3) NOT NULL,
    digital_code_f INT NOT NULL,
    population_f INT NOT NULL,
    square_f INT NOT NULL,
    PRIMARY KEY(id),
    UNIQUE(letter_2_code_f, letter_3_code_f, digital_code_f)
);
