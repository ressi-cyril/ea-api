<?php

namespace App\Tests\UnitTest\User;

use App\Entity\User\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private User $user;

    /**
     * Set up a new User object for each test.
     */
    protected function setUp(): void
    {
        $this->user = new User();
    }

    public function testGettersAndSetters(): void
    {
        // Test Email
        $this->user->setEmail('test@example.com');
        $this->assertEquals('test@example.com', $this->user->getEmail());

        // Test Roles
        $this->user->setRoles(['ROLE_USER']);
        $this->assertEquals(['ROLE_USER'], $this->user->getRoles());

        // Test Password
        $this->user->setPassword('password');
        $this->assertEquals('password', $this->user->getPassword());

        // Test isEnabled
        $this->user->setIsEnabled(true);
        $this->assertTrue($this->user->isEnabled());

        // Test CreatedAt
        $createdAt = new \DateTime();
        $this->user->setCreatedAt($createdAt);
        $this->assertEquals($createdAt, $this->user->getCreatedAt());

        // Test LastLogin
        $lastLogin = new \DateTime();
        $this->user->setLastLogin($lastLogin);
        $this->assertEquals($lastLogin, $this->user->getLastLogin());
    }

}
