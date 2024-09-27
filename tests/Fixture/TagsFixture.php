<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TagsFixture
 */
class TagsFixture extends TestFixture
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
                'id' => 'd57e6dde-c333-4bbe-b1f1-c3bb8f00cf4c',
                'title' => 'Lorem ipsum dolor sit amet',
                'created' => '2024-09-27 07:52:51',
                'modified' => '2024-09-27 07:52:51',
            ],
        ];
        parent::init();
    }
}
