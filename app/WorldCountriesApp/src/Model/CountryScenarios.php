<?php

namespace App\Model;

use App\Model\Exceptions\CountryNotFoundException;
use App\Model\Exceptions\DuplicatedCodeException;
use App\Model\Exceptions\InvalidCodeException;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Config\Definition\Exception\DuplicateKeyException;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

//use Exception;

//CountryScenarios - класс с методамии работы с объектами стран мира
class CountryScenarios
{
   public function __construct(
      private readonly CountryRepository $repository
   ) {}

   //GetAll - показать все страны
   //вход:-
   //выход:-список объектов Coutry
   function GetAll(): array
   {
      return $this->repository->selectAll();
   }
   //GetByCode - показать страны по коду
   //вход: код страны
   //выход: объект извлеченной страны
   //CountryInvalidCodeException, CountryNotFoundExeption
   public function getByCode(string $сode): Country
   {
      //проверяем корректность кода
      if (!$this->validateCode($сode)) {
         throw new InvalidCodeException($сode, 'validation failed');
      }
      // если валидация пройдена, то получить страну по данному коду
      $country = $this->repository->selectByCode($сode);
      if ($country === null) {
         //если страна мира не найдена - выдать ошибку
         throw new CountryNotFoundException($сode);
      }
      return $country;
   }

   //store - сохранить страну
   //вход: объект страны мира
   //выход: - 
   //исключения:InvalidCodeExeption, DuplicatedCodExeption
   function store(Country $country): void
   {
      // выполнить проверку валидности страны 
      $this->validateCountry($country);

      //выполнить проверку уникальности наименования и кодов
      $this->checkUniqueCountryData($country);

      //если проверка пройдена сохранить в БД
      $this->repository->insert(country: $country);
   }

   //delete - удалить страну
   //вход: код удаляемой страны
   //выход: сообщение о успешном удалении
   //исключение: InvalidCodeExeption, CountryNotFoundExeption
   function delete(string $code): void
   {
      //выполнить проверку корректности кода
      if (!$this->validateCode(code: $code)) {
         throw new InvalidCodeException(invalidCode: $code, message: "validation failed");
      }
      //если валидация пройдена, получить страну мира по данному коду
      $country = $this->repository->selectByCode($code);
      if ($country === null) {
         //если страна не найдена - выбросить усключение
         throw new CountryNotFoundException(notFoundCode: $code);
      }
      //удалить и выдать сообщение 
      $this->repository->remove(code: $code);
   }

   //edit - изменить страну
   //вход: код редактируемой страны мира
   //выход: сообщение о успешном редактировании
   //исключение: InvalidCodeExeption, CountryNotFoundExeption, DuplicatedCodExeption
   function edit(string $code, Country $country): void
   {
      //выполнить проверку корректности кода
      if (!$this->validateCode(code: $code)) {
         throw new InvalidCodeException(invalidCode: $code, message: "validation failed");
      }

      // выполнить проверку наличия страны для редактирования
      $existingCountry = $this->repository->selectByCode($code);
      if ($existingCountry === null) {
         throw new CountryNotFoundException(notFoundCode: $code);
      }

      // проверить отсутствие редактирования кодов при его обновлении
      $nonEditableCode = $this->checkCodeLackEditing ($country, $existingCountry);
      if(!$nonEditableCode){
         throw new InvalidCodeException(invalidCode: null, message: "Country codes cannot be edited");
      }

      // Проверка полей на валидность
      $this->validateCountry($country);
    
      //если проверка пройдена, то сделать update
      $this->repository->update(code: $code, country: $country);
   }


   //validateCode -валидность кода страны
   //вход: строка кода страны мира
   //выход: true - если корректно, false - если нет
   //исключение: InvalidCodeExeption
   private function validateCode(string $code): bool
   {
      return preg_match(pattern: '/^([A-Za-z]{2,3}|\d[0-9]{2,3})$/', subject: $code);
   }
   
   //validateCountry -валидность страны
   //вход: строка страны мира
   //выход: true - если корректно, false - если нет 
   //исключение: InvalidCodeExeption
   private function validateCountry(Country $country): void
   {
      $codesCountry = [
         $country->twoLetterCode,
         $country->threeLetterCode,
         $country->digitalCode
      ];
      
      // Проверка корректности кода
      foreach ($codesCountry as $code) {
         if (!$this->validateCode($code)) {
            throw new InvalidCodeException(
               invalidCode: $code,
               message: 'Invalid country code format'
            );
         }
        
         // Проверка наименования страны на null
         if (empty($country->shortName) || strlen(trim($country->fullName))=== 0) {
            throw new InvalidCodeException(
               invalidCode: null,
               message: "Country names must be filled.'"
            );
         }
         //Проверка на отрицательные значения
         if (intval($country->population) < 0 || intval($country->square) < 0) {
            throw new InvalidCodeException(
               invalidCode: null,
               message: 'Population and square must be non-negative'
            );
         }
      }
   } 

   //checkUniqueCountryData -уникальность страны
   //вход: строка страны мира
   //выход: true - если корректно, false - если нет 
   //исключение: DuplicatedCodExeption
   private function checkUniqueCountryData(Country $country): void
   {
      $codesCountry = [
         $country->twoLetterCode,
         $country->threeLetterCode,
         $country->digitalCode
      ];
      // Проверка уникальности кодов
      foreach ($codesCountry as $code) {
         $existingCountry = $this->repository->selectByCode($code);
         if ($existingCountry !== null) {
            throw new DuplicatedCodeException("Country code must be unique");
         }
      }
      // Проверка уникальности именований
      $existingCountryByName = $this->repository->selectByName($country->shortName, $country->fullName);
      if ($existingCountryByName !== null) {
         throw new DuplicatedCodeException('Country names must be unique');
      }
   }

    //checkForDublicateCode - проверка  кодов страны на отсутствие редактирования
   //вход: строка страны мира, редактируемая строка мира
   //выход: true - если корректно, false - если нет 
   private function checkCodeLackEditing  (Country $country, Country $existingCountry): bool{
       
      // проверить отсутствие редактирования кода при его обновлении
       if($country->twoLetterCode !== $existingCountry->twoLetterCode ||
       $country->threeLetterCode !== $existingCountry->threeLetterCode ||
       $country->digitalCode !== $existingCountry->digitalCode){
          return false;
       }
      return true;
   }
}
