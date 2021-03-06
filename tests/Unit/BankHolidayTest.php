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
use Midnite81\BankHolidays\Exceptions\MissingConfigKeyException;
use Midnite81\BankHolidays\Exceptions\TerritoryDoesNotExistException;
use Midnite81\JsonParser\JsonParse;
use Mockery;
use PHPUnit\Framework\TestCase;

class BankHolidayTest extends TestCase
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

    /**
     * @throws MissingConfigKeyException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->client            = Mockery::mock(IClient::class);
        $this->cache             = Mockery::mock(ICache::class);
        $this->fileSystem        = Mockery::mock(IFileSystem::class);
        $this->testJson          = file_get_contents(__DIR__
            . '/../test_data/bank_holiday_data.json');
        $this->testJsonWithoutNI = file_get_contents(__DIR__
            . '/../test_data/bank_holiday_data_without_ni.json');
    }

    /**
     * @test
     * @throws MissingConfigKeyException
     * @throws TerritoryDoesNotExistException
     */
    public function givenDataInTheCacheExpectClientNotCalledAndDataReturned()
    {
        $this->setupCache();
        $this->client->shouldNotHaveReceived('getData');

        $sut = new BankHoliday(
            $this->client,
            $this->cache,
            $this->fileSystem,
            ['cache-key' => 'bank-holiday-test', 'failure-backup' => true]
        );

        $response = $sut->getAll(Territory::ALL);

        $this->assertCount(189, $response);
        $this->assertTrue($response[0] instanceof BankHolidayEntity);
    }

    /**
     * @test
     * @throws MissingConfigKeyException
     * @throws TerritoryDoesNotExistException
     */
    public function givenDataNotInTheCacheExpectClientCalledAndDataReturned()
    {
        $json        = $this->testJson;
        $decodedJson = JsonParse::decode($json);

        $this->cache->shouldReceive('has')->withArgs(['bank-holiday-test'])
            ->andReturn(false);
        $this->cache->shouldNotReceive('get');
        $this->cache->shouldReceive('put')
            ->withArgs(function ($key, $value, $duration) {
                return $key == 'bank-holiday-test'
                    && gettype($value) == 'object'
                    && $duration == 60;
            });
        $this->client->shouldReceive('getData')
            ->andReturn(JsonParse::decode($json));

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

        $response = $sut->getAll(Territory::ALL);

        $this->assertCount(189, $response);
        $this->assertTrue($response[0] instanceof BankHolidayEntity);
    }

    /**
     * @test
     *
     * @throws MissingConfigKeyException
     * @throws TerritoryDoesNotExistException
     */
    public function givenBankHolidayExistsWhenEnglandAndWalesPassedExpectEntityReturned()
    {
        $this->setupCache();
        $newYearsDay = Carbon::create(2020, 01, 01);
        $sut         = new BankHoliday(
            $this->client,
            $this->cache,
            $this->fileSystem,
            [
                'cache-key'      => 'bank-holiday-test',
                'cache-duration' => 60,
                'failure-backup' => true
            ]
        );

        $result = $sut->bankHolidayDetail(
            $newYearsDay,
            Territory::ENGLAND_AND_WALES
        );

        $this->assertTrue($result instanceof BankHolidayEntity);
        $this->assertEquals(
            TerritoryName::ENGLAND_AND_WALES,
            $result->territory
        );
        $this->assertEquals("New Year’s Day", $result->title);
        $this->assertTrue(Carbon::create(2020, 01, 01)->equalTo($newYearsDay));
        $this->assertEquals('', $result->notes);
    }

    /**
     * @test
     *
     * @throws MissingConfigKeyException
     * @throws TerritoryDoesNotExistException
     */
    public function givenBankHolidayExistsWhenIsBankHolidayInvokedExpectTrue()
    {
        $this->setupCache();
        $newYearsDay = Carbon::create(2020, 01, 01);
        $sut         = new BankHoliday(
            $this->client,
            $this->cache,
            $this->fileSystem,
            [
                'cache-key'      => 'bank-holiday-test',
                'cache-duration' => 60,
                'failure-backup' => true
            ]
        );

        $result = $sut->isBankHoliday(
            $newYearsDay,
            Territory::ENGLAND_AND_WALES
        );

        $this->assertTrue($result);
    }

    /**
     * @test
     *
     * @throws MissingConfigKeyException
     * @throws TerritoryDoesNotExistException
     */
    public function givenBankHolidayDoesntExistsWhenIsBankHolidayInvokedExpectTrue()
    {
        $this->setupCache();
        $newYearsDay = Carbon::create(2020, 01, 10);
        $sut         = new BankHoliday(
            $this->client,
            $this->cache,
            $this->fileSystem,
            [
                'cache-key'      => 'bank-holiday-test',
                'cache-duration' => 60,
                'failure-backup' => true
            ]
        );

        $result = $sut->isBankHoliday(
            $newYearsDay,
            Territory::ENGLAND_AND_WALES
        );

        $this->assertFalse($result);
    }

    /**
     * @test
     *
     * @throws MissingConfigKeyException
     * @throws TerritoryDoesNotExistException
     */
    public function givenBankHolidayExistsWhenScotlandPassedExpectEntityReturned()
    {
        $this->setupCache();
        $newYearsDay = Carbon::create(2020, 01, 01);
        $sut         = new BankHoliday(
            $this->client,
            $this->cache,
            $this->fileSystem,
            [
                'cache-key'      => 'bank-holiday-test',
                'cache-duration' => 60,
                'failure-backup' => true
            ]
        );

        $result = $sut->bankHolidayDetail(
            $newYearsDay,
            Territory::SCOTLAND
        );

        $this->assertTrue($result instanceof BankHolidayEntity);
        $this->assertEquals(
            TerritoryName::SCOTLAND,
            $result->territory
        );
        $this->assertEquals("New Year’s Day", $result->title);
        $this->assertTrue(Carbon::create(2020, 01, 01)->equalTo($newYearsDay));
        $this->assertEquals('', $result->notes);
    }

    /**
     * @test
     *
     * @throws MissingConfigKeyException
     * @throws TerritoryDoesNotExistException
     */
    public function givenBankHolidayExistsWhenNorthernIrelandPassedExpectEntityReturned()
    {
        $this->setupCache();
        $newYearsDay = Carbon::create(2020, 01, 01);
        $sut         = new BankHoliday(
            $this->client,
            $this->cache,
            $this->fileSystem,
            [
                'cache-key'      => 'bank-holiday-test',
                'cache-duration' => 60,
                'failure-backup' => true
            ]
        );

        $result = $sut->bankHolidayDetail(
            $newYearsDay,
            Territory::NORTHERN_IRELAND
        );

        $this->assertTrue($result instanceof BankHolidayEntity);
        $this->assertEquals(
            TerritoryName::NORTHERN_IRELAND,
            $result->territory
        );
        $this->assertEquals("New Year’s Day", $result->title);
        $this->assertTrue(Carbon::create(2020, 01, 01)->equalTo($newYearsDay));
        $this->assertEquals('', $result->notes);
    }

    /**
     * @test
     * @throws MissingConfigKeyException
     */
    public function givenTerritoryPassedWhichDoesntExistExpectThrow()
    {
        $this->setupCacheWithoutRegion();
        $newYearsDay = Carbon::create(2020, 01, 01);
        $sut         = new BankHoliday(
            $this->client,
            $this->cache,
            $this->fileSystem,
            [
                'cache-key'      => 'bank-holiday-test',
                'cache-duration' => 60,
                'failure-backup' => true
            ]
        );

        $this->expectException(TerritoryDoesNotExistException::class);

        $result = $sut->bankHolidayDetail(
            $newYearsDay,
            Territory::NORTHERN_IRELAND
        );
    }

    /**
     * @test
     *
     * @throws MissingConfigKeyException
     * @throws TerritoryDoesNotExistException
     */
    public function givenGetMinDateInvokedExpectDateReturned()
    {
        $this->setupCache();
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

        $result = $sut->getMinDate(
            Territory::ENGLAND_AND_WALES
        );

        $this->assertEquals(
            Carbon::create(2015, 01, 01, 0, 0, 0),
            $result
        );
    }

    /**
     * @test
     *
     * @throws MissingConfigKeyException
     * @throws TerritoryDoesNotExistException
     */
    public function givenGetMaxDateInvokedExpectDateReturned()
    {
        $this->setupCache();
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

        $result = $sut->getMaxDate(
            Territory::ENGLAND_AND_WALES
        );

        $this->assertEquals(
            Carbon::create(2021, 12, 28, 0, 0, 0),
            $result
        );
    }

    /**
     * @test
     * @expectedException MissingConfigKeyException
     *
     */
    public function whenConfigIsMissingCacheKeyExpectThrow()
    {
        $this->expectException(MissingConfigKeyException::class);
        if (method_exists($this, 'expectErrorMessage')) {
            $this->expectErrorMessage("The configuration key 'cache-key' is missing");
        }
        $sut = new BankHoliday($this->client, $this->cache, $this->fileSystem,
            []);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    protected function setupCache(): void
    {
        $this->cache->shouldReceive('has')->withArgs(['bank-holiday-test'])
            ->andReturn(true);
        $this->cache->shouldReceive('get')
            ->withArgs(['bank-holiday-test'])
            ->andReturn(JsonParse::decode($this->testJson));
    }

    private function setupCacheWithoutRegion()
    {
        $this->cache->shouldReceive('has')->withArgs(['bank-holiday-test'])
            ->andReturn(true);
        $this->cache->shouldReceive('get')
            ->withArgs(['bank-holiday-test'])
            ->andReturn(JsonParse::decode($this->testJsonWithoutNI));
    }
}