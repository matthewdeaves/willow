<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
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
                'id' => '2d3bf4a6-a2dc-47a4-b817-dd7af2ed485d',
                'is_admin' => 1,
                'email' => 'Lorem ipsum dolor sit amet',
                'password' => 'Lorem ipsum dolor sit amet',
                'image' => '',
                'alt_text' => 'Lorem ipsum dolor sit amet',
                'keywords' => 'Lorem ipsum dolor sit amet',
                'name' => 'Lorem ipsum dolor sit amet',
                'dir' => 'Lorem ipsum dolor sit amet',
                'size' => 1,
                'mime' => 'Lorem ipsum dolor sit amet',
                'created' => '2025-07-12 21:49:10',
                'modified' => '2025-07-12 21:49:10',
                'username' => 'Lorem ipsum dolor sit amet',
                'active' => 1,
            ],
        ];
        parent::init();
    }
}
