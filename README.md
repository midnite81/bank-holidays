# UK Bank Holidays [![Latest Stable Version](https://poser.pugx.org/midnite81/bank-holidays/version)](https://packagist.org/packages/midnite81/bank-holidays) [![Total Downloads](https://poser.pugx.org/midnite81/bank-holidays/downloads)](https://packagist.org/packages/midnite81/bank-holidays) [![Latest Unstable Version](https://poser.pugx.org/midnite81/bank-holidays/v/unstable)](https://packagist.org/packages/midnite81/bank-holidays) [![License](https://poser.pugx.org/midnite81/bank-holidays/license.svg)](https://packagist.org/packages/midnite81/bank-holidays) [![Build](https://travis-ci.org/midnite81/bank-holidays.svg?branch=master)](https://travis-ci.org/midnite81/bank-holidays) [![Coverage Status](https://coveralls.io/repos/github/midnite81/bank-holidays/badge.svg?branch=master)](https://coveralls.io/github/midnite81/bank-holidays?branch=master)

This package integrates with the UK Government's Bank Holiday Json response. It has been principally been
designed for use with laravel, but is framework agnostic under the hood. **This package requires PHP 7.1 or greater.**

## Installation

```
composer require midnite81/bank-holidays
```

If you are using laravel 5.4 or less, you will need to register the Bank Holiday service provider. If you are using 5.5
or greater than the package should be auto discovered.

```
 'providers' => [
      ...
      \Midnite81\BankHolidays\BankHolidayServiceProvider::class,
      ...
  ];
```

You will need to publish the configuration file. To do this, please run

```
php artisan vendor:publish provider="Midnite81\BankHolidays\BankHolidayServiceProvider"
```

## Limitation

The UK Government provides the bank holiday json feed, this at the time of writing only includes the 
years between 2015 and 2021. 

## Versions
|Version|Description|
|:-------|:-----------|
|v2.0 ✅|Php 7.1+|
|v1.0|Php 5.5.9+ Depreciated|

View [changelog](CHANGELOG.md) for changes. 

## Http standards

To adhere to better standards, this package uses the popular and powerful PHP-HTTP library to make HTTP requests. 
This allows you, should you wish, to use your own HTTP Client instead of the default provided with this package. 
For more information on PHP-HTTP, please visit [php-http.org](http://docs.php-http.org/en/latest/).

## Laravel usage example

**Checking a date to see if it's a bank holiday**

```php

use Midnite81\BankHolidays\Contracts\IBankHoliday;

public function myFunction(IBankHoliday $bankHoliday)
{ 
    $bankHoliday = $bankHoliday->bankHolidayDetail(
        \Carbon\Carbon::create(2020, 01, 01), 
        \Midnite81\BankHolidays\Enums\Territory::ENGLAND_AND_WALES
    );

    // if the date provided is a bank holiday a BankHolidayEntity is returned
    // otherwise it returns null.
   
    if ($bankHoliday == null) {
        // the date provided is not a bank holiday
    }
    
    if ($bankHoliday != null) { 
        echo $bankHoliday->title; // returns "New Year's Day"
        echo $bankHoliday->date; // returns Carbon date object    
    } 
}
```

**Get all bank holiday dates**

```php

use Midnite81\BankHolidays\Contracts\IBankHoliday;

public function myFunction(IBankHoliday $bankHoliday)
{ 
    $bankHolidays = $bankHoliday->getAll(\Midnite81\BankHolidays\Enums\Territory::ENGLAND_AND_WALES);

    foreach($bankHolidays as $bankHoliday) { 
        echo $bankHoliday->title . "<br>\n";  
    }
}
```

## Usage without laravel

```php

public function someFunction()
{ 
    $config = [
       'cache-duration' => 60 * 60 * 24,
       'bank-holiday-url' => 'https://www.gov.uk/bank-holidays.json',
       'cache-key' => 'midnite81-bank-holidays',
       'cache-class' => YourCacheClass::class, // you will need to create a cache class 
       'http-client' => null,
       'request-factory' => null
    ];

    $cache = new YourCacheClass(); // this must implement \Midnite81\BankHolidays\Contracts\Drivers\ICache
    $client = new \Midnite81\BankHolidays\Services\Client(null, null, $config);
    $bankHoliday = new \Midnite81\BankHolidays\BankHoliday($client, $cache, $config);

    // Once you have $bankHoliday instantiated you can use the following methods

    $bankHoliday->getAll(int $territory);
    $bankHoliday->bankHolidayDetail(Carbon $date, int $territory);

    // for territory please use the constants in `Midnite81\BankHolidays\Enums\Territory`
}

```

## Bank Holiday Entity

The bank holiday entity has the following properties.

**title** - the title of the holiday - e.g. New Year's Day   
**date** - a carbon instance of the bank holiday date    
**notes** - any notes about the bank holiday    
**bunting** - presumably whether bunting is displayed    
**territory** - the territory the bank holiday applies to    

## Territories

The following territories are available

```php
Midnite81\BankHolidays\Enums\Territory::ENGLAND_AND_WALES; // England and Wales   
Midnite81\BankHolidays\Enums\Territory::SCOTLAND; // Scotland   
Midnite81\BankHolidays\Enums\Territory::NORTHERN_IRELAND; // Northern Ireland   
Midnite81\BankHolidays\Enums\Territory::ALL; // All territories (e.g. England, Wales, Scotland and Northern Ireland)
```