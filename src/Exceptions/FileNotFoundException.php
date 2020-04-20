<?php

namespace Midnite81\BankHolidays\Exceptions;

use Exception;

class FileNotFoundException extends Exception
{
    public function __construct($filename = null)
    {
        $file = basename($filename);
        $message = "Filename '{$file}' was not found";
        parent::__construct($message);
    }
}