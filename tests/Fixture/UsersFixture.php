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
                'id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
                'is_admin' => 1,
                'email' => 'Lorem ipsum dolor sit amet',
                'password' => 'Lorem ipsum dolor sit amet',
                'profile' => '',
                'picture_dir' => 'Lorem ipsum dolor sit amet',
                'picture_size' => 1,
                'picture_type' => 'Lorem ipsum dolor sit amet',
                'created' => '2024-09-27 07:42:10',
                'modified' => '2024-09-27 07:42:10',
                'username' => 'Lorem ipsum dolor sit amet',
                'is_disabled' => 1,
            ],
        ];
        parent::init();
    }
}
