<?php

namespace App\Test\TestCase\View\Helper;

use App\Model\Entity\Vote;
use App\View\Helper\VotesHelper;
use Cake\TestSuite\TestCase;
use Cake\View\View;

/**
 * App\View\Helper\VotesHelper Test Case
 */
class VotesHelperTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\View\Helper\VotesHelper
     */
    public $Votes;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $view = new View();
        $this->Votes = new VotesHelper($view);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Votes);

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

    public function testFormat()
    {
        $expected = Vote::YES;
        $result = $this->Votes->format(true);
        $this->assertSame($expected, $result);

        $expected = Vote::NO;
        $result = $this->Votes->format(false);
        $this->assertSame($expected, $result);
    }
}
