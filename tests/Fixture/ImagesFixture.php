<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ImagesFixture
 */
class ImagesFixture extends TestFixture
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
                'id' => '1202d58b-8fec-4b0c-900b-32246bb64d79',
                'name' => 'Lorem ipsum dolor sit amet',
                'alt_text' => 'Lorem ipsum dolor sit amet',
                'keywords' => 'Lorem ipsum dolor sit amet',
                'image' => '',
                'dir' => 'Lorem ipsum dolor sit amet',
                'size' => 1024,
                'mime' => 'image/png',
                'created' => '2024-10-15 19:58:23',
                'modified' => '2024-10-15 19:58:23',
            ],
            [
                'id' => '2202d58b-8fec-4b0c-900b-32246bb64d79',
                'name' => 'Test image 2',
                'alt_text' => 'Test image 2',
                'keywords' => 'test image 2',
                'image' => '',
                'dir' => 'test2',
                'size' => 2048,
                'mime' => 'image/jpeg',
                'created' => '2024-10-15 19:58:23',
                'modified' => '2024-10-15 19:58:23',
            ],
        ];
        parent::init();
    }
}
