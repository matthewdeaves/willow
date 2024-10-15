<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ModelsImagesFixture
 */
class ModelsImagesFixture extends TestFixture
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
                'id' => '90cf0762-555f-4a7d-9cfb-5f415b65253f',
                'model' => 'Lorem ipsum dolor sit amet',
                'foreign_key' => 'eef66c8a-af81-4dfa-b672-18592db79b64',
                'image_id' => 'a6c2de56-9c86-49c2-9250-efebb8907469',
                'created' => '2024-10-15 17:12:31',
                'modified' => '2024-10-15 17:12:31',
            ],
        ];
        parent::init();
    }
}
