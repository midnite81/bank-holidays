<?php

namespace Midnite81\BankHolidays\Contracts\Drivers;

use Midnite81\BankHolidays\Exceptions\FileNotFoundException;

interface IFileSystem
{
    /**
     * Gets the contents of the specified file
     *
     * @param string $fileName
     *
     * @return string
     * @throws FileNotFoundException
     */
    public function get($fileName): string;
}