<?php
namespace Midnite81\BankHolidays\Contracts\Services;

use Midnite81\BankHolidays\Exceptions\RequestFailedException;
use Midnite81\BankHolidays\Services\Client;
use Psr\Http\Client\ClientExceptionInterface;

interface IClient
{

    /**
     * Gets the bank holiday data
     *
     * @throws RequestFailedException
     */
    public function getData();
}