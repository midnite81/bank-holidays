<?php

namespace Midnite81\BankHolidays\Exceptions;

use Exception;

class MissingConfigKeyException extends Exception
{
    public function __construct($key)
    {
        $message = "The configuration key '{$key}' is missing";
        parent::__construct($message);
    }
}