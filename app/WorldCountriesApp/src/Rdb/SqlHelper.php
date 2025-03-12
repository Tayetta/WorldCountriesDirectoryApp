<?php
namespace App\Rdb;
use mysqli;
use RuntimeException;
class SqlHelper {
    
    //функция проверки доступности БД
    public function pingDb() : void{
        //открыть и закрыть соединение с БД
        $connection = $this->openDbConnection();
        $connection->close();
     }
     //функция соединения с БД
     public function openDbConnection(): mysqli{
        //зададим параметры подключения к БД
        // TODO: вынести и параметры в .env-файл
        $host = 'db';
        $port = 3306;
        $user = 'root';
        $password = 'root';
        $database = 'world_countries_db';
        //создать объект подключения через драйвер
        $connection = new mysqli(
           hostname: $host,
           port: $port,
           username: $user,
           password: $password,
           database: $database,
        );
        //открыть соединение с БД
        if($connection->connect_errno){
           throw new RuntimeException(message:"Couldn't connect to MySQL:" .$connection->connect_error);
        }
        //если все ок - вернуть соединение с БД
        return $connection;
     }
}