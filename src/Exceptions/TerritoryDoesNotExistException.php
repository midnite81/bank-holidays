<?php

namespace Midnite81\BankHolidays\Exceptions;

use Exception;
use Throwable;

class TerritoryDoesNotExistException extends Exception
{
    public function __construct(
        $territory
    ) {
        $message = "The territory {$territory} does not exist";
        parent::__construct($message, 0, null);
    }
}