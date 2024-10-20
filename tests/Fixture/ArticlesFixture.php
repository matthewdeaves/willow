<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ArticlesFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            // Pages
            [
                'id' => '630fe0f3-7d68-472f-a1b1-c73ed3fe0c8e',
                'user_id' => '199b7544-8725-49ee-a26c-a3f32e03e423',
                'title' => 'Page One',
                'slug' => 'page-one',
                'body' => 'Content for Page One',
                'is_published' => 1,
                'created' => '2024-09-27 07:52:35',
                'modified' => '2024-09-27 07:52:35',
                'published' => '2024-09-27 07:53:35',
                'parent_id' => null,
                'lft' => 1,
                'rght' => 10,
                'is_page' => 1,
            ],
            [
                'id' => 'ce6794a2-b191-445c-99ed-de93f6e7eeb1',
                'user_id' => '299b7544-8725-49ee-a26c-a3f32e03e424',
                'title' => 'Page Two',
                'slug' => 'page-two',
                'body' => 'Content for Page Two',
                'is_published' => 1,
                'created' => '2024-09-27 07:53:35',
                'modified' => '2024-09-27 07:53:35',
                'published' => '2024-09-27 07:54:35',
                'parent_id' => '630fe0f3-7d68-472f-a1b1-c73ed3fe0c8e',
                'lft' => 2,
                'rght' => 3,
                'is_page' => 1,
            ],
            [
                'id' => 'de7894a3-c292-556d-00ee-ef04g7f8ffc2',
                'user_id' => '399b7544-8725-49ee-a26c-a3f32e03e425',
                'title' => 'Page Three',
                'slug' => 'page-three',
                'body' => 'Content for Page Three',
                'is_published' => 1,
                'created' => '2024-09-27 07:54:35',
                'modified' => '2024-09-27 07:54:35',
                'published' => '2024-09-27 07:55:35',
                'parent_id' => '630fe0f3-7d68-472f-a1b1-c73ed3fe0c8e',
                'lft' => 4,
                'rght' => 5,
                'is_page' => 1,
            ],
            [
                'id' => 'mn6783j2-l111-445m-99nn-no93p6o7ppk1',
                'user_id' => '299b7544-8725-49ee-a26c-a3f32e03e434',
                'title' => 'Page Seven',
                'slug' => 'page-seven',
                'body' => 'Content for Page Seven',
                'is_published' => 1,
                'created' => '2024-09-27 08:03:35',
                'modified' => '2024-09-27 08:03:35',
                'published' => '2024-09-27 08:04:35',
                'parent_id' => '630fe0f3-7d68-472f-a1b1-c73ed3fe0c8e',
                'lft' => 6,
                'rght' => 7,
                'is_page' => 1,
            ],
            [
                'id' => 'ef8905b4-d393-667e-11ff-fg15h8g9ggd3',
                'user_id' => '499b7544-8725-49ee-a26c-a3f32e03e426',
                'title' => 'Page Four',
                'slug' => 'page-four',
                'body' => 'Content for Page Four',
                'is_published' => 1,
                'created' => '2024-09-27 07:55:35',
                'modified' => '2024-09-27 07:55:35',
                'published' => '2024-09-27 07:56:35',
                'parent_id' => null,
                'lft' => 11,
                'rght' => 16,
                'is_page' => 1,
            ],
            [
                'id' => '5119fb0c-ff60-4c16-9e25-aba3d32d5d5c',
                'user_id' => '599b7544-8725-49ee-a26c-a3f32e03e427',
                'title' => 'Page Five',
                'slug' => 'page-five',
                'body' => 'Content for Page Five',
                'is_published' => 0,
                'created' => '2024-09-27 07:56:35',
                'modified' => '2024-09-27 07:56:35',
                'published' => null,
                'parent_id' => 'ef8905b4-d393-667e-11ff-fg15h8g9ggd3',
                'lft' => 12,
                'rght' => 15,
                'is_page' => 1,
            ],
            [
                'id' => 'e98aaafa-415a-4911-8ff2-25f76b326ea4',
                'user_id' => '699b7544-8725-49ee-a26c-a3f32e03e428',
                'title' => 'Page Six',
                'slug' => 'page-six',
                'body' => 'Content for Page Six',
                'is_published' => 0,
                'created' => '2024-09-27 07:57:35',
                'modified' => '2024-09-27 07:57:35',
                'published' => null,
                'parent_id' => '5119fb0c-ff60-4c16-9e25-aba3d32d5d5c',
                'lft' => 13,
                'rght' => 14,
                'is_page' => 1,
            ],
            // Articles
            [
                'id' => '263a5364-a1bc-401c-9e44-49c23d066a0f',
                'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
                'title' => 'Article One',
                'slug' => 'article-one',
                'body' => 'Content for Article One',
                'is_published' => 1,
                'created' => '2024-09-27 07:58:35',
                'modified' => '2024-09-27 07:58:35',
                'published' => '2024-09-27 07:59:35',
                'parent_id' => null,
                'lft' => 17,
                'rght' => 18,
                'is_page' => 0,
            ],
            [
                'id' => 'ij2349f8-h707-001i-55jj-jk59l2k3kkg7',
                'user_id' => '899b7544-8725-49ee-a26c-a3f32e03e430',
                'title' => 'Article Two',
                'slug' => 'article-two',
                'body' => 'Content for Article Two',
                'is_published' => 1,
                'created' => '2024-09-27 07:59:35',
                'modified' => '2024-09-27 07:59:35',
                'published' => '2024-09-27 08:00:35',
                'parent_id' => null,
                'lft' => 19,
                'rght' => 20,
                'is_page' => 0,
            ],
            [
                'id' => '42655115-cb43-4ba5-bae7-292443b9ce21',
                'user_id' => '999b7544-8725-49ee-a26c-a3f32e03e431',
                'title' => 'Article Three',
                'slug' => 'article-three',
                'body' => 'Content for Article Three',
                'is_published' => 1,
                'created' => '2024-09-27 08:00:35',
                'modified' => '2024-09-27 08:00:35',
                'published' => '2024-09-27 08:01:35',
                'parent_id' => null,
                'lft' => 21,
                'rght' => 22,
                'is_page' => 0,
            ],
            [
                'id' => 'kl4561h0-j909-223k-77ll-lm71n4m5mmi9',
                'user_id' => '099b7544-8725-49ee-a26c-a3f32e03e432',
                'title' => 'Article Four',
                'slug' => 'article-four',
                'body' => 'Content for Article Four',
                'is_published' => 1,
                'created' => '2024-09-27 08:01:35',
                'modified' => '2024-09-27 08:01:35',
                'published' => '2024-09-27 08:02:35',
                'parent_id' => null,
                'lft' => 23,
                'rght' => 24,
                'is_page' => 0,
            ],
            [
                'id' => 'fef07ae2-1b1a-4653-a444-d093e35c6e2f',
                'user_id' => '199b7544-8725-49ee-a26c-a3f32e03e433',
                'title' => 'Article Five',
                'slug' => 'article-five',
                'body' => 'Content for Article Five',
                'is_published' => 0,
                'created' => '2024-09-27 08:02:35',
                'modified' => '2024-09-27 08:02:35',
                'published' => null,
                'parent_id' => null,
                'lft' => 25,
                'rght' => 26,
                'is_page' => 0,
            ],
            [
                'id' => '224310b4-96ad-4d58-a0a9-af6dc7253c4f',
                'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
                'title' => 'Article Six',
                'slug' => 'article-six',
                'body' => 'Content for Article Six',
                'is_published' => 1,
                'created' => '2023-09-27 08:02:35',
                'modified' => '2023-09-27 08:02:35',
                'published' => '2023-09-27 08:03:35',
                'parent_id' => null,
                'lft' => 27,
                'rght' => 28,
                'is_page' => 0,
            ],
        ];
        parent::init();
    }
}
