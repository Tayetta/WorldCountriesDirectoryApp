<?php

namespace App\Model;

use \Datetime;

//Country.php - класс стран
class Country{
    public function __construct(
        public string $shortName,
        public string $fullName,
        public string $twoLetterCode,
        public string $threeLetterCode,
        public string $digitalCode,
        public int $population,
        public int $square
    )
    {
        
    }
}