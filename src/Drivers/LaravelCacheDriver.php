<?php

namespace Midnite81\BankHolidays\Drivers;

use Illuminate\Container\Container;
use Illuminate\Foundation\Application;
use Midnite81\BankHolidays\Contracts\Drivers\ICache;
use Illuminate\Cache\Repository;
use phpDocumentor\Reflection\Types\Mixed_;
use Psr\SimpleCache\InvalidArgumentException;

class LaravelCacheDriver implements ICache
{
    /**
     * @var Repository
     */
    protected $cache;
    /**
     * @var Container
     */
    protected $app;

    /**
     * LaravelCacheDriver constructor.
     *
     * @param Repository $cache
     * @param Application  $app
     */
    public function __construct(Repository $cache, Application $app)
    {
        $this->cache = $cache;
        $this->app = $app;
    }

    /**
     * @inheritDoc
     */
    public function has($key): bool
    {
        return $this->cache->has($key);
    }

    /**
     * @inheritDoc
     */
    public function put($key, $value, $duration): bool
    {
        if (version_compare($this->app->version(), '5.8.0', '<')) {
            $duration = floor($duration * 60);
        }

        return $this->cache->put($key, $value, $duration);
    }

    /**
     * @inheritDoc
     */
    public function get($key)
    {
        return $this->cache->get($key);
    }

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     */
    public function delete($key): bool
    {
        return $this->cache->delete($key);
    }
}