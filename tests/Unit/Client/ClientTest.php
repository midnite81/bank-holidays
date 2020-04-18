<?php

namespace Midnite81\BankHolidays\Tests\Unit\Client;

use GuzzleHttp\Psr7\Request;
use Http\Client\Exception\RequestException;
use Http\Client\HttpClient;
use Midnite81\BankHolidays\Exceptions\MissingConfigKeyException;
use Midnite81\BankHolidays\Exceptions\RequestFailedException;
use Midnite81\BankHolidays\Services\Client;
use Midnite81\BankHolidays\Tests\Integration\LaravelTestCase;
use Mockery;

class ClientTest extends LaravelTestCase
{
    /**
     * @var HttpClient
     */
    protected $httpClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = Mockery::mock(\Http\Client\Curl\Client::class);
    }

    /**
     * @/**
     * @test
     *
     * @throws MissingConfigKeyException
     */
    public function given_config_not_passed_expect_exception_thrown()
    {
        $this->expectException(MissingConfigKeyException::class);
        if (method_exists($this, 'expectErrorMessage')) {
            $this->expectErrorMessage("The configuration key 'bank-holiday-url' is missing");
        }

        $sut = new Client(null, null, []);
    }

    /**
     * @test
     * @throws MissingConfigKeyException
     */
    public function given_response_fails_expect_exception_thrown()
    {
        /**
         * @var HttpClient $httpClient;
         */
        $httpClient = $this->httpClient
            ->shouldReceive('sendRequest')
            ->andThrow(new RequestException(
                'Test Exception',
                new Request('GET', 'http://localhost'),
                null
            ))->getMock();

        $this->expectException(RequestFailedException::class);
        $sut = new Client($httpClient, null, ['bank-holiday-url' => 'https://localhost/']);
        $sut->getData();

        Mockery::close();
    }
}