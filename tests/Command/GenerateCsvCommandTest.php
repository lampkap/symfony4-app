<?php

namespace App\Tests\Command;

use App\Command\GenerateCsvCommand;
use App\Service\HelperService;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GenerateCsvCommandTest extends KernelTestCase
{
    private $helperService;

    protected function setUp()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $this->helperService = self::$container->get('App\Service\HelperService');
    }

    protected static function getMethod($name)
    {
        $class = new ReflectionClass('App\Command\GenerateCsvCommand');
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    public function testIfNumberIsGenerated()
    {
        $result = $this->helperService->generateNumber();

        $this->assertInternalType('integer', $result);
        $this->assertGreaterThanOrEqual(1000, $result);
        $this->assertLessThanOrEqual(30000, $result);
    }

    public function testIfValidDateIsGenerated()
    {
        $result = $this->helperService->generateDate();

        $this->assertNotFalse($this->helperService->formatBirthdate($result));
    }

    public function testIfCsvFileIsCreated()
    {
        $csv = 'test_csv_file_created.csv';

        $command = new GenerateCsvCommand(null, new HelperService());
        $method = self::getMethod('generateCsv');

        $method->invokeArgs($command, array($csv));

        $this->assertFileExists($csv);

        unlink($csv);
    }
}