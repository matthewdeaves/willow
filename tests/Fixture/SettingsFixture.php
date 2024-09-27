<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * SettingsFixture
 */
class SettingsFixture extends TestFixture
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
                'id' => '6509480c-e7e6-4e65-9c38-1423a8d09d01',
                'key_name' => 'host',
                'value' => 'rabbitmq',
                'group_name' => 'RabbitMQ',
                'is_numeric' => 0,
                'created' => '2023-09-27 10:39:23',
                'modified' => '2023-09-27 10:39:23',
            ],
            [
                'id' => '6509480c-e7e6-4e65-9c38-1423a8d09d02',
                'key_name' => 'port',
                'value' => '5672',
                'group_name' => 'RabbitMQ',
                'is_numeric' => 1,
                'created' => '2023-09-27 10:39:23',
                'modified' => '2023-09-27 10:39:23',
            ],
            [
                'id' => '6509480c-e7e6-4e65-9c38-1423a8d09d03',
                'key_name' => 'user',
                'value' => 'admin',
                'group_name' => 'RabbitMQ',
                'is_numeric' => 0,
                'created' => '2023-09-27 10:39:23',
                'modified' => '2023-09-27 10:39:23',
            ],
            [
                'id' => '6509480c-e7e6-4e65-9c38-1423a8d09d04',
                'key_name' => 'password',
                'value' => 'password',
                'group_name' => 'RabbitMQ',
                'is_numeric' => 0,
                'created' => '2023-09-27 10:39:23',
                'modified' => '2023-09-27 10:39:23',
            ],
            [
                'id' => '6509480c-e7e6-4e65-9c38-1423a8d09d05',
                'key_name' => 'tiny',
                'value' => '10',
                'group_name' => 'ImageSizes',
                'is_numeric' => 1,
                'created' => '2023-09-27 10:39:23',
                'modified' => '2023-09-27 10:39:23',
            ],
            [
                'id' => '6509480c-e7e6-4e65-9c38-1423a8d09d06',
                'key_name' => 'small',
                'value' => '50',
                'group_name' => 'ImageSizes',
                'is_numeric' => 1,
                'created' => '2023-09-27 10:39:23',
                'modified' => '2023-09-27 10:39:23',
            ],
            [
                'id' => '6509480c-e7e6-4e65-9c38-1423a8d09d07',
                'key_name' => 'medium',
                'value' => '100',
                'group_name' => 'ImageSizes',
                'is_numeric' => 1,
                'created' => '2023-09-27 10:39:23',
                'modified' => '2023-09-27 10:39:23',
            ],
            [
                'id' => '6509480c-e7e6-4e65-9c38-1423a8d09d08',
                'key_name' => 'large',
                'value' => '300',
                'group_name' => 'ImageSizes',
                'is_numeric' => 1,
                'created' => '2023-09-27 10:39:23',
                'modified' => '2023-09-27 10:39:23',
            ],
            [
                'id' => '6509480c-e7e6-4e65-9c38-1423a8d09d09',
                'key_name' => 'extra-large',
                'value' => '400',
                'group_name' => 'ImageSizes',
                'is_numeric' => 1,
                'created' => '2023-09-27 10:39:23',
                'modified' => '2023-09-27 10:39:23',
            ],
            [
                'id' => '6509480c-e7e6-4e65-9c38-1423a8d09d10',
                'key_name' => 'massive',
                'value' => '500',
                'group_name' => 'ImageSizes',
                'is_numeric' => 1,
                'created' => '2023-09-27 10:39:23',
                'modified' => '2023-09-27 10:39:23',
            ],
            [
                'id' => '6509480c-e7e6-4e65-9c38-1423a8d09d11',
                'key_name' => 'admin_theme',
                'value' => 'AdminTheme',
                'group_name' => 'Theme',
                'is_numeric' => 0,
                'created' => '2023-09-27 10:39:23',
                'modified' => '2023-09-27 10:39:23',
            ],
            [
                'id' => '6509480c-e7e6-4e65-9c38-1423a8d09d12',
                'key_name' => 'default_theme',
                'value' => 'DefaultTheme',
                'group_name' => 'Theme',
                'is_numeric' => 0,
                'created' => '2023-09-27 10:39:23',
                'modified' => '2023-09-27 10:39:23',
            ],
        ];
        parent::init();
    }
}