<?php

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\User;
use Cake\TestSuite\TestCase;

/**
 * Class UserTest
 * @package App\Test\TestCase\Model\Entity
 */
class UserTest extends TestCase
{
    public function testFullName()
    {
        $user = new User();
        $expected = '';
        $result = $user->fullName;
        $this->assertSame($expected, $result);

        $user = new User();
        $user->firstname = 'Foo';
        $expected = 'Foo';
        $result = $user->fullName;
        $this->assertSame($expected, $result);

        $user = new User();
        $user->firstname = 'Foo';
        $user->lastname = 'Bar';
        $expected = 'Foo Bar';
        $result = $user->fullName;
        $this->assertSame($expected, $result);
    }
}
