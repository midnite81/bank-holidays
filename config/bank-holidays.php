<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Duration
    |--------------------------------------------------------------------------
    | This package makes use of the UK government's bank holiday data, rather
    | than requesting the data each time you call this package, data can be
    | cached for a set duration. The cache duration should be set in seconds.
    | If you're using laravel version < 5.8, we will calculate seconds into
    | minutes for the default cache.
    |
    */
    'cache-duration' => 60 * 60 * 24,

    /*
    |--------------------------------------------------------------------------
    | Bank Holiday URL
    |--------------------------------------------------------------------------
    | This is the url where we retrieve the Bank Holiday data from. You should
    | not need to update this, however it's a configurable value just in case.
    */
    'bank-holiday-url' => 'https://www.gov.uk/bank-holidays.json',

    /*
    |--------------------------------------------------------------------------
    | Cache Key
    |--------------------------------------------------------------------------
    | This is the key the data will be stored against in the cache. You do not
    | need to change this unless you believe it will cause conflicts.
    */
    'cache-key' => 'midnite81-bank-holidays',

    /*
    |--------------------------------------------------------------------------
    | Cache Implementation
    |--------------------------------------------------------------------------
    | You can provide your own cache implementation should you wish to, or if
    | you are using this package without laravel. By default, you can use the
    | laravel implementation if you are using laravel.
    */
    'cache-class' => \Midnite81\BankHolidays\Drivers\LaravelCacheDriver::class,

    /*
    |--------------------------------------------------------------------------
    | Http Client
    |--------------------------------------------------------------------------
    | This package uses Php-Http (http://docs.php-http.org/) to make http
    | requests, this enables you to specify a http client class which
    | implements Http\Client\HttpClient. You can leave as null for it to use
    | the default implementation.
    */
    'http-client' => null,

    /*
    |--------------------------------------------------------------------------
    | Request Factory
    |--------------------------------------------------------------------------
    | As above, the package uses Php-Http, and requires a request factory. You
    | can specify a class that implements Http\Message\RequestFactory or you
    | can leave as null to use the default implementation.
    */
    'request-factory' => null
];
