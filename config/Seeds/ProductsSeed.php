<?php
declare(strict_types=1);

use Migrations\BaseSeed;

/**
 * Products seed.
 */
class ProductsSeed extends BaseSeed
{

    /**
     * Get Dependencies.
     *
     * This method returns an array of seed classes that this seed depends on.
     * The order of execution is determined by the order of the dependencies.
     *
     * @return array
     */
    // This method is used to define the order in which seeds should be run.
    // It ensures that the necessary data is available before this seed runs.
    // For example, if this seed depends on users, articles, categories, etc.,
    // those seeds should be listed here in the order they need to be executed.
    public function getDependencies(): array
    {
        return [
            'UsersSeed',
            'CategoriesSeed',
            'ArticlesSeed',
        ];
    }

    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeds is available here:
     * https://book.cakephp.org/migrations/4/en/seeding.html
     *
     */
    public function run(): void
    {
        $data = [
            [
                'product_id' => 1,
                'name' => 'Sample Product',
                'price_usd' => 19.99,
                'category_rating' => 'High',
                'comments' => 'This is a sample product comment.'
            ],
            [
                'product_id' => 2,
                'name' => 'Another Product',
                'price_usd' => 29.99,
                'category_rating' => 'Medium',
                'comments' => 'This is another product comment.'
            ],
            [
                'product_id' => 3,
                'name' => 'Third Product',
                'price_usd' => 39.99,
                'category_rating' => 'Low',
                'comments' => 'This is a third product comment.'
            ],
            [
                'product_id' => 4,
                'name' => 'Fourth Product',
                'price_usd' => 49.99,
                'category_rating' => 'High',
                'comments' => 'This is a fourth product comment.'
            ],
            [
                'product_id' => 5,
                'name' => 'Fifth Product',
                'price_usd' => 59.99,
                'category_rating' => 'Medium',
                'comments' => 'This is a fifth product comment.'
            ]
        ];

        $table = $this->table('products');
        $table->insert($data)->save();
    }
}
