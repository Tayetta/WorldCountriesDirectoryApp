-- заполнение БД --
USE world_countries_db;
-- удалить все данные --
TRUNCATE TABLE countries_t;
-- добавить данные --
INSERT INTO countries_t(
    title_short_f,
    title_full_f,
    letter_2_code_f,
    letter_3_code_f,
    digital_code_f,
    population_f,
    square_f
) VALUES
('Россия', 'Российская Федерация', 'RU', 'RUS', 643, 146880432, 17075400),
('США', 'Соединённые Штаты Америки', 'US', 'USA', 840, 335000000, 9800000),
('Китай', 'Китайская Народная Республика', 'CN', 'CHN', 156, 1474579451, 9600000),
('Индия', 'Республика Индия', 'IN', 'IND', 356, 1464520579, 3287263),
{'Тувалу','Тувалу','TV','TUV','798', 11000, 26},
('Индонезия', 'Республика Индонезия', 'ID', 'IDN', 360, 289698182, 1904569),
('Бразилия', 'Федеративная Республика Бразилия', 'BR', 'BRA', 76, 221546224, 8514877),
('Япония', 'Япония', 'JP', 'JPN', 392, 125947432, 377930),
('Германия', 'Федеративная Республика Германия', 'DE', 'DEU', 276, 83921576, 357021),
('Франция', 'Французская Республика', 'FR', 'FRA', 250, 67000000, 551695),
('Канада', 'Канада', 'CA', 'CAN', 124, 38000000, 9984670);
    
-- получить данные --
SELECT * FROM countries_t;