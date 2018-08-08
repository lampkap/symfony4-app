<?php

namespace App\Tests\Command;

use App\Command\GenerateCsvCommand;
use App\Controller\MemberController;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class GenerateCsvCommandTest extends TestCase
{
    protected static function getMethod($name)
    {
        $class = new ReflectionClass('App\Command\GenerateCsvCommand');
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    public function testIfNumberIsGenerated()
    {
        $command = new GenerateCsvCommand();
        $method = self::getMethod('generateNumber');

        $result = $method->invokeArgs($command, array());

        $this->assertInternalType('integer', $result);
        $this->assertGreaterThanOrEqual(1000, $result);
        $this->assertLessThanOrEqual(30000, $result);
    }

    public function testIfValidDateIsGenerated()
    {
        $command = new GenerateCsvCommand();
        $method = self::getMethod('generateDate');

        $result = $method->invokeArgs($command, array());

        $this->assertNotFalse(MemberController::formatBirthdate($result));
    }

    public function testIfCsvFileIsCreated()
    {
        $csv = '/Users/diem/Desktop/test.csv';

        $command = new GenerateCsvCommand();
        $method = self::getMethod('generateCsv');

        $method->invokeArgs($command, array($csv, 'create'));

        $this->assertFileExists($csv);

        unlink($csv);
    }
}