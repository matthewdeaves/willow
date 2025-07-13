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
                'id' => 'fd992ca9-870e-4f83-a1cf-40f02e956946',
                'model' => 'Lorem ipsum dolor ',
                'foreign_key' => '7cc57fbc-8759-49d6-9f8b-4b36e20b6a91',
                'slug' => 'Lorem ipsum dolor sit amet',
                'created' => 1752353347,
            ],
        ];
        parent::init();
    }
}
