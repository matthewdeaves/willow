<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\AiMetrics;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\AiMetrics Test Case
 */
class AiMetricsTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Entity\AiMetrics
     */
    protected $AiMetrics;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->AiMetrics = new AiMetrics();
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
}
