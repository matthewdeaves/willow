<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * CommentsFixture
 */
class CommentsFixture extends TestFixture
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
                'id' => 'eb03a9fd-50ca-4ef1-a2ef-8f0c4fb8cb13',
                'foreign_key' => '8b5e8283-c6e0-48f9-b455-06fcd882557c',
                'model' => 'Lorem ipsum dolor sit amet',
                'user_id' => '4cc62a9e-ed11-4ad5-b47d-26a97400ebf8',
                'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'display' => 1,
                'is_inappropriate' => 1,
                'is_analyzed' => 1,
                'inappropriate_reason' => 'Lorem ipsum dolor sit amet',
                'created' => '2025-07-12 21:48:38',
                'modified' => '2025-07-12 21:48:38',
            ],
        ];
        parent::init();
    }
}
