<?php

namespace Midnite81\BankHolidays\Contracts;

use Carbon\Carbon;
use Exception;
use Midnite81\BankHolidays\BankHoliday;
use Midnite81\BankHolidays\Entities\BankHolidayEntity;
use Midnite81\BankHolidays\Enums\Territory;
use Midnite81\BankHolidays\Enums\TerritoryName;
use Midnite81\BankHolidays\Exceptions\TerritoryDoesNotExistException;

interface IBankHoliday
{
    /**
     * @param Carbon $date
     * @param int    $territory
     *
     * @return null|BankHolidayEntity
     * @throws TerritoryDoesNotExistException
     * @throws Exception
     */
    public function bankHolidayDetail(Carbon $date, $territory = Territory::ENGLAND_AND_WALES);

    /**
     * @param int $territory
     *
     * @return array
     * @throws TerritoryDoesNotExistException
     * @throws Exception
     */
    public function getAll(int $territory = Territory::ENGLAND_AND_WALES);

    /**
     * @param Carbon $date
     * @param int    $territory
     *
     * @return bool
     * @throws TerritoryDoesNotExistException
     * @throws Exception
     */
    public function isBankHoliday(Carbon $date, $territory = Territory::ENGLAND_AND_WALES);

    /**
     * Returns the earliest date in the data
     *
     * @param int $territory
     *
     * @return Carbon
     * @throws TerritoryDoesNotExistException
     */
    public function getMinDate(int $territory = Territory::ALL): Carbon;

    /**
     * Returns the earliest date in the data
     *
     * @param int $territory
     *
     * @return Carbon
     * @throws TerritoryDoesNotExistException
     */
    public function getMaxDate(int $territory = Territory::ALL): Carbon;
}
