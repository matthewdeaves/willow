<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class CommentsFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'id' => '550e8400-e29b-41d4-a716-446655440000', // UUID for first comment
                'foreign_key' => 'hi1238e7-g606-990h-44ii-ij48k1j2jjf6', // Article One
                'model' => 'Articles',
                'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d02', // user@example.com
                'content' => 'Test comment content',
                'created' => '2024-09-21 08:38:45',
                'modified' => '2024-09-21 08:38:45',
                'display' => 1,
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440001', // UUID for second comment
                'foreign_key' => 'ij2349f8-h707-001i-55jj-jk59l2k3kkg7', // Article Two
                'model' => 'Articles',
                'user_id' => '6509480c-e7e6-4e65-9c38-8574a8d09d02', // user1@example.com
                'content' => 'Another test comment',
                'created' => '2024-09-22 10:15:30',
                'modified' => '2024-09-22 10:56:30',
                'display' => 1,
            ],
            [
                'id' => '5ue8ro00-e29b-41d4-a716-446655447465', // UUID for third comment
                'foreign_key' => 'lm5eeeee-k010-334l-88mm-mn82o5n6nnj0', // Article Six
                'model' => 'Articles',
                'user_id' => 'qwde480c-e7e6-34hy-9c38-8574a8d09d02', // user@example.com
                'content' => 'Do not disable this comment it has to appear on article six.',
                'created' => '2024-09-22 10:00:30',
                'modified' => '2024-09-22 10:15:30',
                'display' => 1,
            ],
            [
                'id' => '550e8445-e29b-41d4-a716-446655447465', // UUID for third comment
                'foreign_key' => 'lm5eeeee-k010-334l-88mm-mn82o5n6nnj0', // Article Six
                'model' => 'Articles',
                'user_id' => 'qwde480c-e7e6-34hy-9c38-8574a8d09d02', // user@example.com
                'content' => 'Do not disable this comment either',
                'created' => '2024-09-22 11:15:30',
                'modified' => '2024-09-22 11:15:30',
                'display' => 1,
            ],
        ];
        parent::init();
    }
}
