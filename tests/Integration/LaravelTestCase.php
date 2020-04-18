<?php
namespace Midnite81\BankHolidays\Tests\Integration;

use Midnite81\BankHolidays\BankHolidayServiceProvider;
use Orchestra\Testbench\TestCase;

abstract class LaravelTestCase extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            BankHolidayServiceProvider::class
        ];
    }
}
