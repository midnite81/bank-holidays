<?php

namespace Midnite81\BankHolidays\Tests\Integration;

use Midnite81\BankHolidays\BankHoliday;
use Midnite81\BankHolidays\Contracts\Drivers\ICache;
use Midnite81\BankHolidays\Contracts\Drivers\IFileSystem;
use Midnite81\BankHolidays\Contracts\IBankHoliday;
use Midnite81\BankHolidays\Contracts\Services\IClient;
use Midnite81\BankHolidays\Drivers\LaravelCacheDriver;
use Midnite81\BankHolidays\Drivers\PhpFileSystem;
use Midnite81\BankHolidays\Services\Client;

class BankHolidayServiceProviderTest extends LaravelTestCase
{
    /**
     * @test
     */
    public function givenIClientIsRegisteredExpectConcreteClass()
    {
        $actual = $this->app->make(IClient::class);

        $this->assertInstanceOf(Client::class, $actual);
    }

    /**
     * @test
     */
    public function givenICacheIsRegisteredExpectConcreteClass()
    {
        $actual = $this->app->make(ICache::class);

        $this->assertInstanceOf(LaravelCacheDriver::class, $actual);
    }

    /**
     * @test
     */
    public function givenIFileSystemIsRegisteredExpectConcreteClass()
    {
        $actual = $this->app->make(IFileSystem::class);

        $this->assertInstanceOf(PhpFileSystem::class, $actual);
    }

    /**
     * @test
     */
    public function givenIBankHolidayIsRegisteredExpectConcreteClass()
    {
        $actual = $this->app->make(IBankHoliday::class);

        $this->assertInstanceOf(BankHoliday::class, $actual);
    }

    /**
     * @test
     */
    public function givenAliasRegisteredExpectConcreteClass()
    {
        $actual = $this->app->make('bank-holiday');

        $this->assertInstanceOf(BankHoliday::class, $actual);
    }
}