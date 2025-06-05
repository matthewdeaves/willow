<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ImageGalleriesImagesFixture
 */
class ImageGalleriesImagesFixture extends TestFixture
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
                'id' => '011e830d-bdea-4a4a-8529-a9a3511723f3',
                'image_gallery_id' => '4843dc7c-c0cb-4e91-9843-2f1dab229df2',
                'image_id' => '7479fa38-2ab2-4170-ab89-ef3812db0d73',
                'position' => 1,
                'caption' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created' => '2025-06-04 08:43:01',
                'modified' => '2025-06-04 08:43:01',
            ],
        ];
        parent::init();
    }
}
