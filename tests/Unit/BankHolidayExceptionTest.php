<?php

namespace Midnite81\BankHolidays\Tests\Unit;

use Carbon\Carbon;
use Midnite81\BankHolidays\BankHoliday;
use Midnite81\BankHolidays\Contracts\Drivers\ICache;
use Midnite81\BankHolidays\Contracts\Drivers\IFileSystem;
use Midnite81\BankHolidays\Contracts\Services\IClient;
use Midnite81\BankHolidays\Entities\BankHolidayEntity;
use Midnite81\BankHolidays\Enums\Territory;
use Midnite81\BankHolidays\Enums\TerritoryName;
use Midnite81\BankHolidays\Exceptions\FileNotFoundException;
use Midnite81\BankHolidays\Exceptions\MissingConfigKeyException;
use Midnite81\BankHolidays\Exceptions\RequestFailedException;
use Midnite81\BankHolidays\Exceptions\TerritoryDoesNotExistException;
use Midnite81\JsonParser\JsonParse;
use Mockery;
use PHPUnit\Framework\TestCase;

class BankHolidayExceptionTest extends TestCase
{
    /**
     * @var BankHoliday
     */
    protected $sut;
    /**
     * @var IClient|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $client;
    /**
     * @var ICache|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $cache;

    /**
     * @var IFileSystem|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $fileSystem;

    /**
     * @var false|string
     */
    protected $testJson;

    /**
     * @var false|string
     */
    protected $testJsonWithoutNI;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client     = Mockery::mock(IClient::class);
        $this->cache      = Mockery::mock(ICache::class);
        $this->fileSystem = Mockery::mock(IFileSystem::class);
    }

    /**
     * Client Exceptions
     */

    /**
     * @test
     * @throws MissingConfigKeyException|TerritoryDoesNotExistException
     */
    public function given_the_client_throws_exception_when_backup_not_used_expect_throw_during_getAll_call()
    {
        $sut = $this->setupClientFailure();

        $this->expectException(RequestFailedException::class);
        $sut->getAll();
    }

    /**
     * @test
     * @throws MissingConfigKeyException
     * @throws TerritoryDoesNotExistException
     */
    public function given_the_client_throws_exception_when_backup_not_used_expect_throw_during_isBankHoliday_call()
    {
        $sut = $this->setupClientFailure();

        $this->expectException(RequestFailedException::class);
        $sut->isBankHoliday(Carbon::now());
    }

    /**
     * @test
     * @throws MissingConfigKeyException
     * @throws TerritoryDoesNotExistException
     */
    public function given_the_client_throws_exception_when_backup_not_used_expect_throw_during_getBankHolidayDetail_call()
    {
        $sut = $this->setupClientFailure();

        $this->expectException(RequestFailedException::class);
        $sut->bankHolidayDetail(Carbon::now());
    }

    /**
     * Filesystem Exceptions
     */

    /**
     * @test
     * @throws MissingConfigKeyException|TerritoryDoesNotExistException
     */
    public function given_the_filesystem_throws_exception_when_backup_not_used_expect_throw_during_getAll_call()
    {
        $sut = $this->setUpFileSystemFailure();

        $this->expectException(FileNotFoundException::class);
        $sut->getAll();
    }

    /**
     * @test
     * @throws MissingConfigKeyException
     * @throws TerritoryDoesNotExistException
     */
    public function given_the_filesystem_throws_exception_when_backup_not_used_expect_throw_during_isBankHoliday_call()
    {
        $sut = $this->setUpFileSystemFailure();

        $this->expectException(FileNotFoundException::class);
        $sut->isBankHoliday(Carbon::now());
    }

    /**
     * @test
     * @throws MissingConfigKeyException
     * @throws TerritoryDoesNotExistException
     */
    public function given_the_filesystem_throws_exception_when_backup_not_used_expect_throw_during_getBankHolidayDetail_call()
    {
        $sut = $this->setUpFileSystemFailure();

        $this->expectException(FileNotFoundException::class);
        $sut->bankHolidayDetail(Carbon::now());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    /**
     * @return BankHoliday
     * @throws MissingConfigKeyException
     */
    protected function setupClientFailure(): BankHoliday
    {
        $this->client->shouldReceive('getData')
            ->andThrow(new RequestFailedException());

        $this->cache->shouldReceive('has')
            ->andReturn(false);

        $sut = new BankHoliday(
            $this->client,
            $this->cache,
            $this->fileSystem,
            [
                'cache-key'      => 'bank-holiday-test',
                'cache-duration' => 60,
                'failure-backup' => false
            ]
        );

        return $sut;
    }

    /**
     * @return BankHoliday
     * @throws MissingConfigKeyException
     */
    protected function setupFileSystemFailure(): BankHoliday
    {
        $this->client->shouldReceive('getData')
            ->andThrow(new RequestFailedException());

        $this->cache->shouldReceive('has')
            ->andReturn(false);

        $this->fileSystem->shouldReceive('get')
            ->andThrow(FileNotFoundException::class);

        $sut = new BankHoliday(
            $this->client,
            $this->cache,
            $this->fileSystem,
            [
                'cache-key'      => 'bank-holiday-test',
                'cache-duration' => 60,
                'failure-backup' => true
            ]
        );

        return $sut;
    }
}
