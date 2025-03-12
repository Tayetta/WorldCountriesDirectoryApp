<?php

namespace App\Rdb;

use App\Model\CountryRepository;
use App\Model\Country;
use App\Rdb\SqlHelper;
use Exception;

//константы кодов
enum searheCode: string
{
  case TWO_LETTER_CODE = "letter_2_code_f";
  case THREE_LETTER_CODE = "letter_3_code_f";
  case DIGITAL_CODE = "digital_code_f";
}
//CountryStorage - имплементирует интерфейс CountryRepository хранилища стран
class CountryStorage implements CountryRepository
{
  public function __construct(
    private readonly SqlHelper $sqlHelper
  ) {
    $this->sqlHelper->pingDb();
  }

  //selectAll - показать все страны
  public function selectAll(): array
  {
    try {

      //создадим подключение
      $connection = $this->sqlHelper->openDbConnection();
      //подготовим строку запроса
      $queryStr = 'SELECT title_short_f, title_full_f, letter_2_code_f, letter_3_code_f, digital_code_f, population_f, square_f
      FROM countries_t';
      //выполнить запрос
      $rows = $connection->query(query: $queryStr);
      //считать результат запроса 
      $countries = [];
      while ($row = $rows->fetch_array()) {
        $country = new Country(
          shortName: $row[0],
          fullName: $row[1],
          twoLetterCode: $row[2],
          threeLetterCode: $row[3],
          digitalCode: $row[4],
          population: $row[5],
          square: $row[6]
        );
        array_push($countries, $country);
      }
      //вернем полученнный результат
      return $countries;
    } finally {
      //закрыть соединение БД
      if (isset($connection)) {
        $connection->close();
      }
    }
  }

  //selectByCode - показать страну по заданному коду 
  public function selectByCode(string $code): ?Country
  {
    try {
      //определим параметр запроса
      $code = strtoupper(trim($code));
      $param = $this->setSearchParameter($code);      
      //соединение
      $connection = $this->sqlHelper->openDbConnection();
      // строка запроса
      $queryStr = "SELECT title_short_f, title_full_f, letter_2_code_f, letter_3_code_f, digital_code_f, population_f, square_f
      FROM countries_t
      WHERE $param = ? ";
      //запрос
      $query = $connection->prepare(query: $queryStr);
      $query->bind_param("s", $code);
      //выполнить запрос
      $query->execute();
      $rows = $query->get_result();
      //считать результат запроса в цикле
      while ($row = $rows->fetch_array()) {
        return new Country(
          shortName: $row[0],
          fullName: $row[1],
          twoLetterCode: $row[2],
          threeLetterCode: $row[3],
          digitalCode: $row[4],
          population: intval($row[5]),
          square: intval($row[6])
        );
      }
      return null;
    } finally {
      //закрыть соединение БД
      if (isset($connection)) {
        $connection->close();
      }
    }
  }

  //selectByName - показать страну по имени
  public function selectByName(string $shortName, string $fullName): ?Country
  {
    try {
      // Соединение с базой данных
      $connection = $this->sqlHelper->openDbConnection();

      // Строка запроса для проверки уникальности именований
      $queryStr = "
          SELECT title_short_f, title_full_f, letter_2_code_f, letter_3_code_f, digital_code_f, population_f, square_f
          FROM countries_t
          WHERE title_short_f = ? OR title_full_f = ?
      ";

      // Подготовка запроса
      $query = $connection->prepare($queryStr);
      $query->bind_param("ss", $shortName, $fullName);

      // Выполнение запроса
      $query->execute();
      $rows = $query->get_result();

      // Чтение результата запроса
      while ($row = $rows->fetch_array()) {
        return new Country(
          shortName: $row[0],
          fullName: $row[1],
          twoLetterCode: $row[2],
          threeLetterCode: $row[3],
          digitalCode: $row[4],
          population: intval($row[5]),
          square: intval($row[6])
        );
      }
      return null;
    } finally {
      // Закрытие соединения с базой данных
      if (isset($connection)) {
        $connection->close();
      }
    }
  }


  //save - сохранить страну
  public function insert(Country $country): void
  {
    try {
      //создать подключение к БД
      $connection = $this->sqlHelper->openDbConnection();
      // строка запроса
      $queryStr = "INSERT INTO countries_t (title_short_f, title_full_f, letter_2_code_f, letter_3_code_f, digital_code_f, population_f, square_f)
      VALUES (?,?,?,?,?,?,?);";
      //запрос
      $query = $connection->prepare(query: $queryStr);
      $query->bind_param(
        'sssssii',
        $country->shortName,
        $country->fullName,
        $country->twoLetterCode,
        $country->threeLetterCode,
        $country->digitalCode,
        $country->population,
        $country->square,
      );
      //выполнить запрос 
      if (!$query->execute()) {
        throw new Exception(message: 'insert execute failed');
      }
    } finally {
      //если соединение с БД есть закроем его
      if (isset($connection)) {
        $connection->close();
      }
    }
  }
  //delete - удалить страну
  public function remove(string $code): void
  {
    try {
      //создать подключение к БД
      $connection = $this->sqlHelper->openDbConnection();
      //определим параметр запроса
      $code = strtoupper(trim($code));
      $param = $this->setSearchParameter($code);
      // строка запроса
      $queryStr = "DELETE FROM countries_t WHERE $param = ?";
      //запрос
      $query = $connection->prepare(query: $queryStr);
      $query->bind_param('s', $code);
      //выполнить запрос 
      if (!$query->execute()) {
        throw new Exception(message: 'delete execute failed');
      }
    } finally {
      //если соединение с БД есть закроем его
      if (isset($connection)) {
        $connection->close();
      }
    }
  }
  //update - изменить страну
  public function update(string $code, Country $country): void
  {
    try {
      //создать подключение к БД
      $connection = $this->sqlHelper->openDbConnection();
      //определим параметр запроса
      $code = strtoupper(trim($code));
      $param = $this->setSearchParameter($code);
      // строка запроса
      $queryStr = "UPDATE countries_t SET 
      title_short_f =?, 
      title_full_f = ?,
      letter_2_code_f = ?,
      letter_3_code_f = ?,
      digital_code_f = ?,
      population_f = ?,
      square_f = ?
      WHERE $param = ?";
      //запрос
      $query = $connection->prepare(query: $queryStr);
      $query->bind_param(
        'sssssiis',
        $country->shortName,
        $country->fullName,
        $country->twoLetterCode,
        $country->threeLetterCode,
        $country->digitalCode,
        $country->population,
        $country->square,
        $code,
      );
      //выполнить запрос 
      if (!$query->execute()) {
        throw new Exception(message: 'update execute failed');
      }
    } finally {
      //если соединение с БД есть закроем его
      if (isset($connection)) {
        $connection->close();
      }
    }
  }
  //определим параметр для запроса
  private function setSearchParameter($code): string
  {
    $param = "";
    if (strlen($code) == 2 && preg_match("/^[A-Z]{2}$/", $code)) {
      $param = searheCode::TWO_LETTER_CODE->value;
    } else if (strlen($code) == 3 && preg_match("/^[A-Z]{3}$/", $code)) {
      $param = searheCode::THREE_LETTER_CODE->value;
    } else if (preg_match("/^[0-9]{2,3}$/", $code)) {
      $param = searheCode::DIGITAL_CODE->value;
    }
    return $param;
  }
}
