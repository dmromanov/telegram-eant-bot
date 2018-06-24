<?php
namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Chat;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\Chat Test Case
 */
class ChatTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Entity\Chat
     */
    public $Chat;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->Chat = new Chat();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Chat);

        parent::tearDown();
    }

    /**
     * Test initial setup
     *
     * @return void
     */
    public function testInitialization()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
