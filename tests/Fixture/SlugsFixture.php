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
                'id' => 1,
                'article_id' => 1,
                'slug' => 'Lorem ipsum dolor sit amet',
                'active' => 1,
                'created' => 1728811267,
                'modified' => '2024-10-13 09:21:07',
            ],
        ];
        parent::init();
    }
}
