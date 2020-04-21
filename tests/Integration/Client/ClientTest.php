<?php

namespace Midnite81\Tests;

use Midnite81\BankHolidays\Contracts\Services\IClient;
use Midnite81\BankHolidays\Exceptions\MissingConfigKeyException;
use Midnite81\BankHolidays\Exceptions\RequestFailedException;
use Midnite81\BankHolidays\Services\Client;
use Midnite81\BankHolidays\Tests\Integration\LaravelTestCase;

class ClientTest extends LaravelTestCase
{
    /**
     * @/**
     * @test
     * @throws MissingConfigKeyException
     * @throws RequestFailedException
     */
    public function givenDataRequestedExpectResponse()
    {
        $client = new Client(
            null,
            null,
            ['bank-holiday-url' => 'https://www.gov.uk/bank-holidays.json']
        );

        $data = $client->getData();

        $this->assertTrue(gettype($data) == 'object');
        $this->assertTrue(property_exists($data, 'england-and-wales'));
    }

    /**
     * @test
     */
    public function givenLaravelInstantiationExpectResponse()
    {
        $client = $this->app->make(IClient::class);

        $data = $client->getData();

        $this->assertTrue(gettype($data) == 'object');
        $this->assertTrue(property_exists($data, 'england-and-wales'));
    }
}
