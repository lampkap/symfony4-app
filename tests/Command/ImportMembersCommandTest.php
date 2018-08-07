<?php

namespace App\Tests\Command;

use App\Command\ImportMembersCommand;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ImportMembersCommandTest extends TestCase
{
    protected static function getMethod($name)
    {
        $class = new ReflectionClass('App\Command\ImportMembersCommand');
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    public function testIfCsvIsParsed()
    {
        $csv = '/Users/diem/Desktop/members_2.csv';
        $command = new ImportMembersCommand();
        $method = self::getMethod('parseCsv');

        $result = $method->invokeArgs($command, array($csv));

        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
    }
}