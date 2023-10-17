<?php

namespace App\Tests\UnitTest\User;

use App\Entity\User\StaffUser;
use PHPUnit\Framework\TestCase;

class StaffUserTest extends TestCase
{
    private StaffUser $staffUser;

    /**
     * Set up a new StaffUser object for each test.
     */
    protected function setUp(): void
    {
        $this->staffUser = new StaffUser();
    }

    public function testGettersAndSetters(): void
    {
        // Test Name
        $this->staffUser->setName('Name');
        $this->assertEquals('Name', $this->staffUser->getName());
    }

}
