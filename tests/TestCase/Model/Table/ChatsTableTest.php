<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ChatsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ChatsTable Test Case
 */
class ChatsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\ChatsTable
     */
    public $Chats;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.chats'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Chats') ? [] : ['className' => ChatsTable::class];
        $this->Chats = TableRegistry::get('Chats', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Chats);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
