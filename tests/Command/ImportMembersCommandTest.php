<?php

namespace App\Tests\Command;

use App\Command\GenerateCsvCommand;
use App\Command\ImportMembersCommand;
use Symfony\Component\HttpKernel\Log\Logger;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ImportMembersCommandTest extends TestCase
{
    protected static function getMethod($class, $name)
    {
        $class = new ReflectionClass($class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    public function testIfCsvIsParsed()
    {
        $csv = 'test_csv_parser.csv';

        $generateCommand = new GenerateCsvCommand();
        $method = self::getMethod('App\Command\GenerateCsvCommand', 'generateCsv');

        $method->invokeArgs($generateCommand, array($csv));

        $this->assertFileExists($csv);

        $command = new ImportMembersCommand(null, new Logger());
        $method = self::getMethod('App\Command\ImportMembersCommand', 'parseCsv');

        $result = $method->invokeArgs($command, array($csv));

        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);

        unlink($csv);
    }
}