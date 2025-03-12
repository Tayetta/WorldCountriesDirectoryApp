<?php

namespace App\Controller;

use App\Model\Country;
use App\Model\CountryScenarios;
use App\Model\Exceptions\CountryNotFoundException;
use App\Model\Exceptions\InvalidCodeException;
use App\Model\Exceptions\DuplicatedCodeException;
use Exception;
use JsonException;
use LDAP\Result;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\DuplicateKeyException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[Route(path: 'api/country', name: 'app_api_country')]
final class CountryController extends AbstractController
{
    public function __construct(
        private readonly CountryScenarios $countries
    ) {}
    //показать все страны
    #[Route(path: ' ', name: 'app_api_country', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $countriesPreview = [];
        foreach ($this->countries->getAll() as $country) {
            $countryPreview = $this->buildCountriesPreview(country: $country);
            array_push($countriesPreview, $countryPreview);
        }
        return $this->json(data: $countriesPreview, status: 200);
    }

    //показать данные страны по коду
    #[Route(path:'/{code}', name:'app_api_country_code', methods: ['GET'])]
   public function getByCode(string $code): JsonResponse{
        try {
            $country = $this->countries->getByCode($code);
            return $this->json(data: $country);
        } catch (InvalidCodeException $ex) {
            $response = $this->buildErrorResponse(ex: $ex);
            $response->setStatusCode(code: 400);
            return $response;
        } catch (CountryNotFoundException $ex) {
            $response = $this->buildErrorResponse(ex: $ex);
            $response->setStatusCode(code: 404);
            return $response;
        }
    }

    //добавить данные страны
    #[Route(path: '', name: 'app_api_country_add', methods: ['POST'])]
    public function add(#[MapRequestPayload] Country $country): JsonResponse
    {
        try {
            $this->countries->store(country: $country);
            $countryPreview = $this->buildCountriesPreview(country: $country);
            return $this->json($countryPreview,status: 204);
        } catch (InvalidCodeException $ex) {
            $response = $this->buildErrorResponse(ex: $ex);
            $response->setStatusCode(code: 400);
            return $response;
        } catch (DuplicatedCodeException $ex) {
            $response = $this->buildErrorResponse(ex: $ex);            
            $response->setStatusCode(code: 409);
            return $response;
        }
    }

    // удалить данные о стране
    #[Route(path: '/{code}', name: 'app_api_country_remove', methods: ['DELETE'])]
    public function delete(string $code): JsonResponse
    {
       try{       
        $this->countries->delete($code);        
        return $this->json(data:"Deletion completed successfully",status:200);
       }catch(InvalidCodeException $ex){
        $response = $this->buildErrorResponse(ex: $ex);
        $response->setStatusCode(code: 400);
        return $response;
       }catch(CountryNotFoundException $ex){
        $response = $this->buildErrorResponse(ex: $ex);
        $response->setStatusCode(code: 404);
        return $response;
       }
    }

    //изменить данные о стране
    #[Route(path: '/{code}', name: 'app_api_country_edit', methods: ['PATCH'])]
    public function edit(string $code, #[MapRequestPayload] Country $country): JsonResponse
    {
        try {
            $this->countries->edit(code: $code, country: $country);
            $countriesPreview = $this->buildCountriesPreview($country);
            return $this->json(data: $countriesPreview , status: 200);
        } catch (InvalidCodeException $ex) {
            $response = $this->buildErrorResponse(ex: $ex);
            $response->setStatusCode(code: 400);
            return $response;
        } catch (CountryNotFoundException $ex) {
            $response = $this->buildErrorResponse(ex: $ex);
            $response->setStatusCode(code: 404);
            return $response;
        } catch (DuplicatedCodeException $ex) {
            $response = $this->buildErrorResponse(ex: $ex);
            $response->setStatusCode(code: 409);
            return $response;
        }
    }

    // вспомогательный метод формирования ошибки
    private function buildErrorResponse(Exception $ex): JsonResponse
    {
        return $this->json(data: [
            'errorCode' => $ex->getCode(),
            'errorMessage' => $ex->getMessage(),
        ]);
    }
   
    //вспомогательный метод  получения данных о стране
    private function buildCountriesPreview(Country $country): Country
    {
        return new Country(
            shortName: $country->shortName,
            fullName: $country->fullName,
            twoLetterCode: $country->twoLetterCode,
            threeLetterCode: $country->threeLetterCode,
            digitalCode: $country->digitalCode,
            population: $country->population,
            square: $country->square
        );
    }
}
