<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\AiMetricsController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\AiMetricsController Test Case
 *
 * @link \App\Controller\AiMetricsController
 */
class AiMetricsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.AiMetrics',
    ];

    /**
    * Test index method
    *
    * @return void
    */
   public function testIndex(): void
   {
       $this->get('/admin/ai-metrics');
       $this->assertResponseOk();
   }

    /**
     * Test view method
     *
     * @return void
     * @link \App\Controller\AiMetricsController::view()
     */
    public function testView(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test add method
     *
     * @return void
     * @link \App\Controller\AiMetricsController::add()
     */
    public function testAdd(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test edit method
     *
     * @return void
     * @link \App\Controller\AiMetricsController::edit()
     */
    public function testEdit(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test delete method
     *
     * @return void
     * @link \App\Controller\AiMetricsController::delete()
     */
    public function testDelete(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
