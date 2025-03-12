<?php

namespace App\Model;

//CountryRepository - интерфейс хранилища стран
interface CountryRepository{
    //selectAll - показать все страны
    function selectAll():array;    
    //selectByCode - показать страны по двухбуквенному коду
    function selectByCode(string $code): ?Country;       
    //selectByName - найти страну по наименованию
    function selectByName(string $shortName, string $fullName): ?Country;
    //save - сохранить страну
    function insert(Country $country): void;
    //delete - удалить страну
   function remove(string $code): void;
    //update - изменить страну
    function update(string $code, Country $country): void;
}