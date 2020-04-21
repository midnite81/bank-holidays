<?php

namespace Midnite81\BankHolidays\Tests\Integration\Drivers;

use Midnite81\BankHolidays\Drivers\PhpFileSystem;
use Midnite81\BankHolidays\Exceptions\FileNotFoundException;
use PHPUnit\Framework\TestCase;

class PhpFileSystemTest extends TestCase
{
    /**
     * @var PhpFileSystem
     */
    protected $sut;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sut = new PhpFileSystem();
    }

    /**
     * @test
     */
    public function givenGetInvokedExpectFileContentsRetrieved()
    {
        $path = join(DIRECTORY_SEPARATOR, ['..', '..', '..', 'backup', 'bank-holiday.json']);

        $result = $this->sut->get(__DIR__ . DIRECTORY_SEPARATOR . $path);

        $this->assertStringContainsString('{"england-and-wales":{"division":"england-and-wales",', $result);
    }

    /**
     * @test
     * @expectedException \Midnite81\BankHolidays\Exceptions\FileNotFoundException
     */
    public function givenGetInvokedWhenFileNotAvailableExpectThrow()
    {
        $this->expectException(FileNotFoundException::class);
        $result = $this->sut->get('some-file-that-doesnt-exist.json');
    }
}