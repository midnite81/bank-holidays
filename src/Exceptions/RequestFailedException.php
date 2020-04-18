<?php

namespace Midnite81\BankHolidays\Exceptions;

use Exception;
use Throwable;

class RequestFailedException extends Exception
{
    public function __construct(
        Throwable $previous = null
    ) {
        parent::__construct("The data request failed", 0, $previous);
    }
}