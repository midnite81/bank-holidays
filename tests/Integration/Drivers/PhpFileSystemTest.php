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
    public function given_get_invoked_expect_file_contents_retrieved()
    {
        $path = join(DIRECTORY_SEPARATOR, ['..', '..', '..', 'backup', 'bank-holiday.json']);

        $result = $this->sut->get(__DIR__ . DIRECTORY_SEPARATOR . $path);

        $this->assertStringContainsString('{"england-and-wales":{"division":"england-and-wales",', $result);
    }

    /**
     * @test
     * @expectedException \Midnite81\BankHolidays\Exceptions\FileNotFoundException
     */
    public function given_get_invoked_when_file_not_available_expect_throw()
    {
        $this->expectException(FileNotFoundException::class);
        $result = $this->sut->get('some-file-that-doesnt-exist.json');
    }
}