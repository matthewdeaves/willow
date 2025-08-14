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
     * @link \App\Model\Table\AiMetricsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
 * Get total cost by date range
 */
public function getCostsByDateRange(string $startDate, string $endDate): float
{
    $result = $this->find()
        ->where(['created >=' => $startDate, 'created <=' => $endDate])
        ->select(['total' => 'SUM(cost_usd)'])
        ->first();
        
    return (float)($result->total ?? 0);
}
/**
 * Get metrics summary by task type
 */
public function getTaskTypeSummary(string $startDate, string $endDate): array
{
    return $this->find()
        ->select([
            'task_type',
            'count' => 'COUNT(*)',
            'avg_time' => 'AVG(execution_time_ms)',
            'success_rate' => 'AVG(success) * 100',
            'total_cost' => 'SUM(cost_usd)',
            'total_tokens' => 'SUM(tokens_used)'
        ])
        ->where(['created >=' => $startDate, 'created <=' => $endDate])
        ->groupBy('task_type')
        ->toArray();
}
/**
 * Get recent error logs
 */
public function getRecentErrors(int $limit = 10): array
{
    return $this->find()
        ->where(['success' => false])
        ->orderBy(['created' => 'DESC'])
        ->limit($limit)
        ->toArray();
}
}
