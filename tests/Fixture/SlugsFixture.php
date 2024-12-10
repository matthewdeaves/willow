<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * SlugsFixture
 */
class SlugsFixture extends TestFixture
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
                'id' => 'aaeb88d8-2590-42b3-8050-05083c79c8d1',
                'model' => 'Lorem ipsum dolor ',
                'foreign_key' => 'a80d7be5-8b4b-4f7a-a237-03f4c7366035',
                'slug' => 'Lorem ipsum dolor sit amet',
                'created' => 1733856964,
            ],
        ];
        parent::init();
    }
}
