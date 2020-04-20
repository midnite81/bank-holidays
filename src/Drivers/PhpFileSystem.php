<?php

namespace Midnite81\BankHolidays\Drivers;

use Midnite81\BankHolidays\Contracts\Drivers\IFileSystem;
use Midnite81\BankHolidays\Exceptions\FileNotFoundException;

class PhpFileSystem implements IFileSystem
{
    /**
     * Gets the contents of the specified file
     *
     * @param string $fileName
     *
     * @return string
     * @throws FileNotFoundException
     */
    public function get($fileName)
    {
        $contents = @file_get_contents($fileName);

        if (!$contents) {
            throw new FileNotFoundException($fileName);
        }

        return $contents;
    }
}
