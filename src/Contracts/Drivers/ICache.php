<?php

namespace Midnite81\BankHolidays\Contracts\Drivers;

use phpDocumentor\Reflection\Types\Mixed_;

interface ICache
{
    /**
     * Returns a boolean as to whether the key passed exists in the cache
     *
     * @param $key
     *
     * @return bool
     */
    public function has($key): bool;

    /**
     * Adds a key/value to the cache for a specified duration
     *
     * @param $key
     * @param $value
     * @param $duration
     *
     * @return bool
     */
    public function put($key, $value, $duration): bool;

    /**
     * Gets the value of the specified key from the cache
     *
     * @param $key
     *
     * @return mixed
     */
    public function get($key);

    /**
     * Deletes the key from the cache
     *
     * @param $key
     *
     * @return bool
     */
    public function delete($key): bool;
}
