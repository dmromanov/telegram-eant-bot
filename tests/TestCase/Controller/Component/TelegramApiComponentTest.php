<?php
namespace App\Test\TestCase\Controller\Component;

use App\Controller\Component\TelegramApiComponent;
use Cake\Controller\ComponentRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\Component\TelegramApiComponent Test Case
 */
class TelegramApiComponentTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Controller\Component\TelegramApiComponent
     */
    public $TelegramApi;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $registry = new ComponentRegistry();
        $this->TelegramApi = new TelegramApiComponent($registry);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->TelegramApi);

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
