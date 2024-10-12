<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AipromptsFixture
 */
class AipromptsFixture extends TestFixture
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
                'id' => '5f1a33b2-a011-444d-bb26-1ea290395ae1',
                'task_type' => 'Lorem ipsum dolor sit amet',
                'system_prompt' => 'You are a good bot and you like to go beep bop bip',
                'model' => 'Lorem ipsum dolor sit amet',
                'max_tokens' => 1,
                'temperature' => 1,
                'created_at' => 1728732013,
                'modified_at' => 1728732013,
            ],
        ];
        parent::init();
    }
}
