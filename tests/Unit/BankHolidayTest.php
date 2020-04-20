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
    public function given_data_in_the_cache_expect_client_not_called_and_data_returned()
    {
        $this->setupCache();
        $this->client->shouldNotHaveReceived('getData');

        $sut = new BankHoliday(
            $this->client,
            $this->cache,
            $this->fileSystem,
            ['cache-key' => 'bank-holiday-test', 'failed-backup' => true]
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
    public function given_data_not_in_the_cache_expect_client_called_and_data_returned()
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
                'failed-backup'  => true
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
    public function given_bank_holiday_exists_when_england_and_wales_passed_expect_entity_returned()
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
                'failed-backup'  => true
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
    public function given_bank_holiday_exists_when_scotland_passed_expect_entity_returned()
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
                'failed-backup'  => true
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
    public function given_bank_holiday_exists_when_northern_ireland_passed_expect_entity_returned()
    {
        $this->setupCache();
        $newYearsDay = Carbon::create(2020, 01, 01);
        $sut         = new BankHoliday(
            $this->client,
            $this->cache,
            $this->fileSystem,
            ['cache-key'      => 'bank-holiday-test',
             'cache-duration' => 60,
             'failed-backup'  => true
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
     */
    public function given_territory_passed_which_doesnt_exist_expect_throw()
    {
        $this->setupCacheWithoutRegion();
        $newYearsDay = Carbon::create(2020, 01, 01);
        $sut         = new BankHoliday(
            $this->client,
            $this->cache,
            $this->fileSystem,
            ['cache-key'      => 'bank-holiday-test',
             'cache-duration' => 60,
             'failed-backup'  => true
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
     * @expectedException MissingConfigKeyException
     *
     */
    public function when_config_is_missing_cache_key_expect_throw()
    {
        $this->expectException(MissingConfigKeyException::class);
        if (method_exists($this, 'expectErrorMessage')) {
            $this->expectErrorMessage("The configuration key 'cache-key' is missing");
        }
        $sut = new BankHoliday($this->client, $this->cache, $this->fileSystem, []);
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