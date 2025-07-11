<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AiMetricsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\AiMetricsTable Test Case
 */
class AiMetricsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\AiMetricsTable
     */
    protected $AiMetrics;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.AiMetrics',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('AiMetrics') ? [] : ['className' => AiMetricsTable::class];
        $this->AiMetrics = $this->getTableLocator()->get('AiMetrics', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->AiMetrics);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\AiMetricsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
