<?php

namespace Midnite81\BankHolidays\Entities;

use Carbon\Carbon;

class BankHolidayEntity
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var Carbon
     */
    public $date;

    /**
     * @var string
     */
    public $notes;

    /**
     * @var bool
     */
    public $bunting;

    /**
     * @var string
     */
    public $territory;

    public function __construct(
        string $title = null,
        Carbon $date = null,
        string $notes = null,
        bool $bunting = null,
        string $territory = null
    ) {
        $this->title   = $title;
        $this->date    = $date;
        $this->notes   = $notes;
        $this->bunting = $bunting;
        $this->territory = $territory;
    }
}
