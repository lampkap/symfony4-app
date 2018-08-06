<?php

namespace App\Tests\Controller;

use App\Controller\MemberController;
use PHPUnit\Framework\TestCase;

class MemberControllerTest extends TestCase
{
    public function testIfDateStringCanBeFormattedToDate()
    {
        $dateString = '17-05-1993 ';
        $result = MemberController::formatBirthdate($dateString);

        $this->assertEquals(trim($dateString), $result->format('d-m-Y'));
    }

    public function testIfDateStringCanNotBeFormattedToDate()
    {
        $dateString = ' ';
        $result = MemberController::formatBirthdate($dateString);

        $this->assertFalse($result);
    }

    public function testIfMemberIsValid()
    {
        $controller = new MemberController();
        $data = array('number' => '12345 ', 'birthdate' => '17-05-1993');
        $result = $controller->validateMember($data);

        $this->assertEmpty($result);
    }

    public function testIfMemberHasNoNumber()
    {
        $controller = new MemberController();
        $data = array('number' => ' ', 'birthdate' => '17-05-1993');
        $result = $controller->validateMember($data);

        $this->assertNotEmpty($result);
        $this->assertEquals($result[0]['message'], 'Gelieve jouw lidnummer in te vullen');
    }

    public function testIfMemberHasNoValidNumber()
    {
        $controller = new MemberController();
        $data = array('number' => 'string', 'birthdate' => '17-05-1993');
        $result = $controller->validateMember($data);

        $this->assertNotEmpty($result);
        $this->assertEquals($result[0]['message'], 'Het lidnummer mag enkel nummers bevatten');
    }

    public function testIfMemberHasNoDate()
    {
        $controller = new MemberController();
        $data = array('number' => 12345, 'birthdate' => ' ');
        $result = $controller->validateMember($data);

        $this->assertNotEmpty($result);
        $this->assertEquals($result[0]['message'], 'Gelieve jouw geboortedatum in te vullen');
    }

    public function testIfMemberHasNoValidDate()
    {
        $controller = new MemberController();
        $data = array('number' => '12345', 'birthdate' => 'test');
        $result = $controller->validateMember($data);

        $this->assertNotEmpty($result);
        $this->assertEquals($result[0]['message'], 'Gelieve een geldig geboortedatum in te vullen');
    }
}