<?php

namespace Midnite81\BankHolidays;

use Carbon\Carbon;
use Exception;
use Midnite81\BankHolidays\Contracts\Drivers\ICache;
use Midnite81\BankHolidays\Contracts\Drivers\IFileSystem;
use Midnite81\BankHolidays\Contracts\IBankHoliday;
use Midnite81\BankHolidays\Contracts\Services\IClient;
use Midnite81\BankHolidays\Entities\BankHolidayEntity;
use Midnite81\BankHolidays\Enums\Territory;
use Midnite81\BankHolidays\Enums\TerritoryName;
use Midnite81\BankHolidays\Exceptions\FileNotFoundException;
use Midnite81\BankHolidays\Exceptions\MissingConfigKeyException;
use Midnite81\BankHolidays\Exceptions\RequestFailedException;
use Midnite81\BankHolidays\Exceptions\TerritoryDoesNotExistException;
use Midnite81\JsonParser\JsonParse;
use phpDocumentor\Reflection\Types\Object_;
use test\Mockery\MagicParams;

class BankHoliday implements IBankHoliday
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var IClient
     */
    protected $client;

    /**
     * @var ICache
     */
    protected $cache;

    /**
     * @var object
     */
    protected $data;

    /**
     * @var Exception
     */
    protected $systemFailure;
    /**
     * @var IFileSystem
     */
    protected $fileSystem;

    /**
     * BankHoliday constructor.
     *
     * @param IClient     $client
     * @param ICache      $cache
     * @param IFileSystem $fileSystem
     * @param array       $config
     *
     * @throws MissingConfigKeyException
     */
    public function __construct(
        IClient $client,
        ICache $cache,
        IFileSystem $fileSystem,
        array $config = []
    ) {
        $this->client = $client;
        $this->cache  = $cache;
        $this->fileSystem = $fileSystem;
        $this->processConfig($config);
        $this->data = $this->hasOrGetData();
    }

    /**
     * @param Carbon $date
     * @param int    $territory
     *
     * @return bool
     * @throws TerritoryDoesNotExistException
     * @throws Exception
     */
    public function isBankHoliday(
        Carbon $date,
        $territory = Territory::ENGLAND_AND_WALES
    ): bool {
        $this->checkForExceptions();

        return $this->bankHolidayDetail($date, $territory) != null;
    }

    /**
     * @param Carbon $date
     * @param int    $territory
     *
     * @return null|BankHolidayEntity
     * @throws TerritoryDoesNotExistException
     * @throws Exception
     */
    public function bankHolidayDetail(
        Carbon $date,
        $territory = Territory::ENGLAND_AND_WALES
    ): ?BankHolidayEntity {
        $this->checkForExceptions();

        $england_and_wales = $this->getBankHolidayEntity(
            $date,
            $territory,
            Territory::ENGLAND_AND_WALES,
            TerritoryName::ENGLAND_AND_WALES,
            TerritoryName::ENGLAND_AND_WALES_KEY
        );

        if ($england_and_wales != null) {
            return $england_and_wales;
        }

        $scotland = $this->getBankHolidayEntity(
            $date,
            $territory,
            Territory::SCOTLAND,
            TerritoryName::SCOTLAND,
            TerritoryName::SCOTLAND_KEY
        );

        if ($scotland != null) {
            return $scotland;
        }

        $northernIreland = $this->getBankHolidayEntity(
            $date,
            $territory,
            Territory::NORTHERN_IRELAND,
            TerritoryName::NORTHERN_IRELAND,
            TerritoryName::NORTHERN_IRELAND_KEY
        );

        if ($northernIreland != null) {
            return $northernIreland;
        }

        return null;
    }

    /**
     * @param int $territory
     *
     * @return array
     * @throws TerritoryDoesNotExistException
     * @throws Exception
     */
    public function getAll(int $territory = Territory::ENGLAND_AND_WALES): array
    {
        $this->checkForExceptions();

        $events = [];

        if (in_array(
            $territory,
            [Territory::ENGLAND_AND_WALES, Territory::ALL]
        )
        ) {
            $data   = $this->getRegionalData(
                TerritoryName::ENGLAND_AND_WALES_KEY,
                TerritoryName::ENGLAND_AND_WALES
            );
            $events = array_merge($events, $data);
        }

        if (in_array(
            $territory,
            [Territory::SCOTLAND, Territory::ALL]
        )
        ) {
            $data   = $this->getRegionalData(
                TerritoryName::SCOTLAND_KEY,
                TerritoryName::SCOTLAND
            );
            $events = array_merge($events, $data);
        }

        if (in_array(
            $territory,
            [Territory::NORTHERN_IRELAND, Territory::ALL]
        )
        ) {
            $data   = $this->getRegionalData(
                TerritoryName::NORTHERN_IRELAND_KEY,
                TerritoryName::NORTHERN_IRELAND
            );
            $events = array_merge($events, $data);
        }

        return $events;
    }

    /**
     * @param array $config
     *
     * @throws MissingConfigKeyException
     */
    protected function processConfig(array $config): void
    {
        if (!array_key_exists('cache-key', $config)) {
            throw new MissingConfigKeyException('cache-key');
        }

        if (!array_key_exists('failure-backup', $config)) {
            throw new MissingConfigKeyException('failure-backup');
        }

        $this->config = $config;
    }

    /**
     * @param Carbon $date
     * @param        $territory
     * @param        $searchTerritory
     * @param        $territoryName
     * @param        $territoryKey
     *
     * @return BankHolidayEntity|null
     * @throws TerritoryDoesNotExistException
     */
    protected function getBankHolidayEntity(
        Carbon $date,
        $territory,
        $searchTerritory,
        $territoryName,
        $territoryKey
    ): ?BankHolidayEntity {
        $carbonKey = $date->format('Y-m-d');

        if (in_array(
            $territory,
            [$searchTerritory, Territory::ALL]
        )
        ) {
            foreach ($this->regionalData($territoryKey) as $holidayItem) {
                if ($holidayItem->date == $carbonKey) {
                    return new BankHolidayEntity(
                        $holidayItem->title,
                        Carbon::createFromFormat('Y-m-d', $holidayItem->date),
                        $holidayItem->notes,
                        $holidayItem->bunting,
                        $territoryName
                    );
                }
            }
        }

        return null;
    }

    /**
     * @return mixed
     */
    protected function hasOrGetData(): ?\stdClass
    {
        if ($this->cache->has($this->config['cache-key'])) {
            return $this->cache->get($this->config['cache-key']);
        }

        try {
            $data = $this->client->getData();
        } catch (RequestFailedException $e) {
            if ($this->config['failure-backup']) {
                try {
                    $data
                        = JsonParse::decode($this->fileSystem->get($this->getBackupFile()));
                } catch (FileNotFoundException $e) {
                    $this->systemFailure = $e;
                    return null;
                }
            } else {
                $this->systemFailure = $e;
                return null;
            }
        }

        $this->cache->put(
            $this->config['cache-key'],
            $data,
            $this->config['cache-duration']
        );

        return $data;
    }

    /**
     * @param $territory
     *
     * @return array
     * @throws TerritoryDoesNotExistException
     */
    protected function regionalData($territory): array
    {
        if (!property_exists($this->data, $territory)) {
            throw new TerritoryDoesNotExistException($territory);
        }

        return $this->data->{$territory}->events;
    }

    /**
     * @param $key
     * @param $name
     *
     * @return array
     * @throws TerritoryDoesNotExistException
     */
    protected function getRegionalData($key, $name): array
    {
        $data = $this->regionalData($key);

        foreach ($data as $key => $item) {
            $data[$key] = new BankHolidayEntity(
                $item->title,
                Carbon::createFromFormat('Y-m-d', $item->date),
                $item->notes,
                $item->bunting,
                $name
            );
        }

        return $data;
    }

    /**
     * @return string
     */
    protected function getBackupFile(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '../backup/bank-holiday.json';
    }

    /**
     * @throws Exception
     */
    protected function checkForExceptions(): void
    {
        if ($this->systemFailure) {
            throw $this->systemFailure;
        }
    }
}
