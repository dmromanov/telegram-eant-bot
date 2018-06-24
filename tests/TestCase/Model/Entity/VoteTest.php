<?php
namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Vote;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\Vote Test Case
 */
class VoteTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Entity\Vote
     */
    public $Vote;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->Vote = new Vote();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Vote);

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
