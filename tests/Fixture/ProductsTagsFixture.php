<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ProductsTagsFixture
 */
class ProductsTagsFixture extends TestFixture
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
                'product_id' => 'ee79d587-10c6-4a3f-b9d5-a43db4fa4107',
                'tag_id' => '74ac18bd-38aa-457a-9af4-b1d38aeaeff3',
            ],
        ];
        parent::init();
    }
}
