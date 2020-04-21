<?php

namespace Midnite81\BankHolidays\Tests\Integration\Drivers;

use Midnite81\BankHolidays\Drivers\LaravelCacheDriver;
use Midnite81\BankHolidays\Tests\Integration\LaravelTestCase;
use Illuminate\Cache\Repository;
use Mockery;

class LaravelCacheDriverTest extends LaravelTestCase
{
    /**
     * @var Repository
     */
    protected $laravelCache;

    /**
     * @var LaravelCacheDriver
     */
    protected $sut;

    /**
     * @var string
     */
    protected $testKey;

    /**
     * @var string
     */
    protected $testValue;

    /**
     * @var \Illuminate\Foundation\Application|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $mockedApplication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockedApplication
            = Mockery::mock('Illuminate\Foundation\Application');
        $this->laravelCache = app('cache.store');
        $this->sut = new LaravelCacheDriver(
            $this->laravelCache,
            $this->mockedApplication
        );
        $this->testKey = 'test-key';
        $this->testValue = 'B37CU55';
    }

    /**
     * @test
     */
    public function givenKeyExistsExpectTrue()
    {
        $this->laravelCache->put('test-key', '1', 60);

        $result = $this->sut->has('test-key');

        $this->assertTrue($result);

        $this->laravelCache->delete('test-key');
    }

    /**
     * @test
     */
    public function givenKeyDoesNotExistsExpectFalse()
    {
        $result = $this->sut->has('test-key');

        $this->assertFalse($result);
    }

    /**
     * @test
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function givenKeyIsSetExpectStored()
    {
        $this->laravelCache->put($this->testKey, $this->testValue, 60);

        $value = $this->sut->get($this->testKey);

        $this->assertEquals($value, $this->testValue);

        $this->laravelCache->delete($this->testKey);
    }

    /**
     * @test
     */
    public function givenKeyExistsWhenDeletedExpectDeleted()
    {
        $this->laravelCache->put($this->testKey, $this->testValue, 60);

        $this->sut->delete($this->testKey);

        $this->assertFalse($this->laravelCache->has($this->testKey));
    }

    /**
     * @test
     */
    public function givenLaravelVersionBefore58ExpectSecondsToMinutesConversion()
    {
        $this->mockedApplication->shouldReceive('version')->andReturn('5.7.5')->once();
        $mockedRepository = Mockery::mock(\Illuminate\Cache\Repository::class)
            ->shouldReceive('put')
            ->withArgs([$this->testKey, $this->testValue, 3600])
            ->andReturn(1)
            ->getMock();

        $sut = new LaravelCacheDriver(
            $mockedRepository,
            $this->mockedApplication
        );
        $sut->put($this->testKey, $this->testValue, 60);
    }

    /**
     * @test
     */
    public function givenLaravelVersionAfterOrEqualTo58ExpectSecondsToMinutesConversion()
    {
        $this->mockedApplication->shouldReceive('version')->andReturn('5.8.6');
        $mockedRepository = Mockery::mock(\Illuminate\Cache\Repository::class)
            ->shouldReceive('put')
            ->withArgs([$this->testKey, $this->testValue, 60])
            ->andReturn(1)
            ->getMock();

        $sut = new LaravelCacheDriver(
            $mockedRepository,
            $this->mockedApplication
        );
        $sut->put($this->testKey, $this->testValue, 60);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
