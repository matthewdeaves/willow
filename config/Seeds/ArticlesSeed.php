<?php
declare(strict_types=1);

use Migrations\BaseSeed;

/**
 * Articles seed.
 */
class ArticlesSeed extends BaseSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeds is available here:
     * https://book.cakephp.org/migrations/4/en/seeding.html
     *
     * @return void
     */
    public function run(): void
    {
        $data = [
            [
                'id' => '16d41cc2-fd65-485b-abd6-934a8a36a14e',
                'user_id' => '4f850b86-50c8-41cf-b6f8-abbee04969d1',
                'kind' => 'page',
                'featured' => 0,
                'title' => 'All about Adapters',
                'lede' => 'adapters-lede',
                'slug' => 'adapters',
                'body' => '<p>Adapters body paragraph text</p>',
                'markdown' => '<p>Adapters body paragraph text</p>',
                'summary' => 'summary text',
                'image' => NULL,
                'alt_text' => NULL,
                'keywords' => NULL,
                'name' => NULL,
                'dir' => NULL,
                'size' => NULL,
                'mime' => NULL,
                'is_published' => 1,
                'created' => '2025-07-01 21:54:32',
                'modified' => '2025-07-01 21:54:52',
                'published' => '2025-07-01 21:54:52',
                'meta_title' => 'adapters',
                'meta_description' => 'adapters metadata',
                'meta_keywords' => 'adapters',
                'facebook_description' => '',
                'linkedin_description' => '',
                'instagram_description' => '',
                'twitter_description' => '',
                'word_count' => 4,
                'parent_id' => NULL,
                'lft' => 1,
                'rght' => 2,
                'main_menu' => 0,
                'view_count' => 3,
            ],
            [
                'id' => '3e164e5c-2704-4135-b703-d216e6fcfa41',
                'user_id' => '4f850b86-50c8-41cf-b6f8-abbee04969d1',
                'kind' => 'article',
                'featured' => 1,
                'title' => 'Adapters Post',
                'lede' => 'adapters-post-lede',
                'slug' => 'adapters-post',
                'body' => '<p>adapters post body text</p>',
                'markdown' => NULL,
                'summary' => 'summary',
                'image' => NULL,
                'alt_text' => NULL,
                'keywords' => NULL,
                'name' => NULL,
                'dir' => NULL,
                'size' => NULL,
                'mime' => NULL,
                'is_published' => 1,
                'created' => '2025-07-01 21:56:07',
                'modified' => '2025-07-01 21:56:07',
                'published' => '2025-07-01 21:56:07',
                'meta_title' => 'adapters-post',
                'meta_description' => '',
                'meta_keywords' => '',
                'facebook_description' => '',
                'linkedin_description' => '',
                'instagram_description' => '',
                'twitter_description' => '',
                'word_count' => 4,
                'parent_id' => NULL,
                'lft' => 3,
                'rght' => 4,
                'main_menu' => 0,
                'view_count' => 1,
            ],
        ];

        $table = $this->table('articles');
        $table->insert($data)->save();
    }
}
