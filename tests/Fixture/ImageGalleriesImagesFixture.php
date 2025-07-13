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
                'id' => '6a32d011-7871-463b-9b63-7e1ec650ebaf',
                'image_gallery_id' => 'bf8b409d-1bbf-4798-a3ce-c43b63eccb87',
                'image_id' => '470fdf29-fb04-4336-a8e4-cb86678cfb60',
                'position' => 1,
                'caption' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created' => '2025-07-12 21:48:41',
                'modified' => '2025-07-12 21:48:41',
            ],
        ];
        parent::init();
    }
}
