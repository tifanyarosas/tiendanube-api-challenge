<?php

namespace App\Exceptions;

class NotFoundException extends \Exception {

    public function __construct()
    {
        parent::__construct('Element not found ');
    }
}
