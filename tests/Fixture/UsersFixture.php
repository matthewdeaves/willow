<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Authentication\PasswordHasher\DefaultPasswordHasher;
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
        $hasher = new DefaultPasswordHasher();
        $this->records = [
            [
                'id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
                'email' => 'admin@example.com',
                'username' => 'admin@example.com',
                'password' => $hasher->hash('password'),
                'is_admin' => 1,
                'is_disabled' => 0,
                'created' => '2023-09-27 10:39:23',
                'modified' => '2023-09-27 10:39:23',
            ],
            [
                'id' => '6509480c-e7e6-4e65-9c38-1423a8d09d02',
                'email' => 'user@example.com',
                'username' => 'user@example.com',
                'password' => $hasher->hash('password'),
                'is_admin' => 0,
                'is_disabled' => 0,
                'created' => '2023-09-27 10:39:23',
                'modified' => '2023-09-27 10:39:23',
            ],
            [
                'id' => '6509480c-e7e6-4e65-9c38-1423a8d09d03',
                'email' => 'disabled@example.com',
                'username' => 'disabled@example.com',
                'password' => $hasher->hash('password'),
                'is_admin' => 0,
                'is_disabled' => 1,
                'created' => '2023-09-27 10:39:23',
                'modified' => '2023-09-27 10:39:23',
            ],
        ];
        parent::init();
    }
}
