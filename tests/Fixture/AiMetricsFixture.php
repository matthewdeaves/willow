<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AiMetricsFixture
 */
class AiMetricsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => '5db9d4c4-d706-45a5-810a-8269eac3cef9',
                'task_type' => 'Lorem ipsum dolor sit amet',
                'execution_time_ms' => 1,
                'tokens_used' => 1,
                'cost_usd' => 1.5,
                'success' => 1,
                'error_message' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'model_used' => 'Lorem ipsum dolor sit amet',
                'created' => '2025-08-14 18:40:02',
                'modified' => '2025-08-14 18:40:02',
            ],
        ];
        parent::init();
    }
}
