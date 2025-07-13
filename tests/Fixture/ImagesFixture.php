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
                'id' => 'f3db978e-d41d-4ade-9e64-ebcaf4322b0c',
                'name' => 'Lorem ipsum dolor sit amet',
                'alt_text' => 'Lorem ipsum dolor sit amet',
                'keywords' => 'Lorem ipsum dolor sit amet',
                'image' => '',
                'dir' => 'Lorem ipsum dolor sit amet',
                'size' => 1,
                'mime' => 'Lorem ipsum dolor sit amet',
                'created' => '2025-07-12 21:48:42',
                'modified' => '2025-07-12 21:48:42',
            ],
        ];
        parent::init();
    }
}
